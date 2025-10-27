<?php

namespace App\Http\Controllers\Employee;

use App\DTOs\Fortnox\Employee\UpdateFortnoxEmployeeRequestDTO;
use App\DTOs\Role\RoleResponseDTO;
use App\DTOs\User\UpdateUserRequestDTO;
use App\DTOs\User\UserResponseDTO;
use App\Enums\PermissionsEnum;
use App\Enums\User\UserStatusEnum;
use App\Http\Controllers\User\BaseUserController;
use App\Http\Traits\ResponseTrait;
use App\Models\Role;
use App\Models\Schedule;
use App\Models\SubscriptionStaffDetails;
use App\Models\User;
use App\Services\Fortnox\FortnoxEmployeeService;
use Auth;
use DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class EmployeeAccountController extends BaseUserController
{
    use ResponseTrait;

    /**
     * Additional fields to include in the response.
     */
    private array $includes = [
        'info',
        'employee',
        'roles',
    ];

    /**
     * Send only these fields in the response.
     */
    private array $onlys = [
        'id',
        'firstName',
        'lastName',
        'email',
        'formattedCellphone',
        'identityNumber',
        'status',
        'createdAt',
        'updatedAt',
        'info.timezone',
        'info.language',
        'roles.id',
        'roles.name',
        'employee.isValidIdentity',
        'deletedAt',
    ];

    /**
     * Display the index view.
     */
    public function index(): Response
    {
        $queries = $this->getQueries(
            [
                'roles_name_notIn' => 'Customer,Company',
            ],
            defaultFilter: [
                'status_eq' => UserStatusEnum::Active(),
            ],
            pagination: 'page',
            show: 'all'
        );

        $paginatedData = User::applyFilterSortAndPaginate(
            $queries,
            fields: $this->onlys,
        );

        return Inertia::render('Employee/Overview/index', [
            'employees' => UserResponseDTO::transformCollection(
                $paginatedData->data,
                $this->includes,
                onlys: $this->onlys
            ),
            'roles' => $this->getRoles(),
            'pagination' => array_keys_to_camel_case($paginatedData->pagination),
        ]);
    }

    private function getRoles()
    {
        $onlys = [
            'id',
            'name',
        ];
        $hiddenRoles = ['Customer', 'Company'];

        if (! Auth::user()->can(PermissionsEnum::EmployeeRolesAppointSuperadmin())) {
            $hiddenRoles[] = 'Superadmin';
        }

        $roles = Role::selectWithRelations($onlys)
            ->whereNotIn('name', $hiddenRoles)
            ->orderBy('id')
            ->get();

        return RoleResponseDTO::collection($roles)->only(...$onlys);
    }

    /**
     * Display the index view as json.
     */
    public function jsonIndex(): JsonResponse
    {
        $queries = $this->getQueries(
            [
                'roles_name_notIn' => 'Customer,Company',
            ],
        );
        $paginatedData = User::applyFilterSortAndPaginate($queries);

        return $this->successResponse(
            UserResponseDTO::transformCollection($paginatedData->data)
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUserRequestDTO $request, User $user, FortnoxEmployeeService $fortnox): RedirectResponse
    {
        // add validation if user not employee
        if (! $user->isEmployee()) {
            throw new NotFoundHttpException();
        }

        $identityNumber = $request->isNotOptional('identity_number') ?
            $request->identity_number : $user->identity_number;
        $firstName = $request->isNotOptional('first_name') ? $request->first_name : $user->first_name;
        $lastName = $request->isNotOptional('last_name') ? $request->last_name : $user->last_name;
        $phones = $request->isNotOptional('cellphone') ? explode(' ', $request->cellphone) : [];
        $dialCode = $request->isNotOptional('cellphone') ? str_replace('+', '', $phones[0]) : $user->dial_code;
        $cellphone = $request->isNotOptional('cellphone') ? $dialCode.$phones[1] : $user->cellphone;
        $email = $request->isNotOptional('email') ? $request->email : $user->email;

        $response = $fortnox->updateEmployee(
            $user->employee->fortnox_id ?? '',
            UpdateFortnoxEmployeeRequestDTO::from([
                'personal_identity_number' => $identityNumber,
                'first_name' => $firstName,
                'last_name' => $lastName,
                'address1' => $user->employee->address->address,
                'address2' => $user->employee->address->address_2,
                'post_code' => $user->employee->address->postal_code,
                'city' => $user->employee->address->city->name,
                'country' => $user->employee->address->city->country->name,
                'phone1' => $cellphone,
                'email' => $email,
            ])
        );

        DB::transaction(function () use ($request, $response, $user, $cellphone, $dialCode) {
            $user->update([
                ...$request->toArray(),
                'cellphone' => $cellphone,
                'dial_code' => $dialCode,
            ]);
            $user->info->update($request->toArray());
            $user->employee?->update([
                'fortnox_id' => $response->employee_id,
                'identity_number' => $user->identity_number,
                'name' => $user->full_name,
                'email' => $user->email,
                'phone1' => $user->cellphone,
                'dial_code' => $user->dial_code,
                'is_valid_identity' => $user->identity_number === $response->personal_identity_number,
            ]);
        });

        return back()->with('success', __('employee updated successfully'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user): RedirectResponse
    {
        // add validation if user not employee
        if (! $user->isEmployee()) {
            throw new NotFoundHttpException();
        }

        $exists = $user->scheduleEmployees()->active()->exists();

        if ($exists) {
            return back()->with('error', __('employee has active schedules'));
        }

        DB::transaction(function () use ($user) {
            $user->status = UserStatusEnum::Deleted();
            $user->save();
            $user->delete();
            $user->employee->delete();
            SubscriptionStaffDetails::where('user_id', $user->id)->delete();

            $schedules = Schedule::with('scheduleEmployees')
                ->whereHas('scheduleEmployees', function (Builder $query) use ($user) {
                    $query->where('user_id', $user->id)
                        ->where('status', 'pending');
                })
                ->get();

            foreach ($schedules as $schedule) {
                $endTime = calculate_end_time(
                    $schedule->start_at,
                    calculate_calendar_quarters(
                        $schedule->quarters,
                        $schedule->scheduleEmployees->count() - 1
                    ),
                    format: 'Y-m-d H:i:s'
                );

                $schedule->update([
                    'end_at' => $endTime,
                ]);
            }

            $user->scheduleEmployees()
                ->withTrashed()
                ->where('status', 'pending')
                ->forceDelete();
        });

        return back()->with('success', __('employee deleted successfully'));
    }

    /**
     * Restore the specified resource from storage.
     */
    public function restore(User $user): RedirectResponse
    {
        // add validation if user not employee
        if (! $user->isEmployee()) {
            throw new NotFoundHttpException();
        }

        DB::transaction(function () use ($user) {
            $user->status = UserStatusEnum::Active();
            $user->save();
            $user->restore();
            $user->employee()->withTrashed()->restore();
            SubscriptionStaffDetails::withTrashed()->where('user_id', $user->id)->restore();
        });

        return back()->with('success', __('employee restored successfully'));
    }
}
