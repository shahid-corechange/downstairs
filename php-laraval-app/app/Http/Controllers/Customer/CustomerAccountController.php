<?php

namespace App\Http\Controllers\Customer;

use App\DTOs\Credit\CreditResponseDTO;
use App\DTOs\Customer\CustomerResponseDTO;
use App\DTOs\Property\PropertyResponseDTO;
use App\DTOs\User\UpdateUserRequestDTO;
use App\DTOs\User\UserResponseDTO;
use App\Enums\GlobalSetting\GlobalSettingEnum;
use App\Enums\MembershipTypeEnum;
use App\Enums\User\UserStatusEnum;
use App\Http\Controllers\User\BaseUserController;
use App\Http\Traits\ResponseTrait;
use App\Models\Credit;
use App\Models\Customer;
use App\Models\Property;
use App\Models\User;
use App\Services\CreditService;
use DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response as HttpResponse;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CustomerAccountController extends BaseUserController
{
    use ResponseTrait;

    /**
     * Additional fields to include in the response.
     */
    private array $includes = [
        'info',
    ];

    /**
     * Send only these fields in the response.
     */
    private array $onlys = [
        'id',
        'firstName',
        'lastName',
        'fullname',
        'identityNumber',
        'email',
        'formattedCellphone',
        'status',
        'createdAt',
        'updatedAt',
        'deletedAt',
        'info.timezone',
        'info.language',
        'info.notificationMethod',
    ];

    /**
     * Display the index view.
     */
    public function index(): Response
    {
        $queries = $this->getQueries(
            [
                'roles_name_in' => 'Customer',
            ],
            defaultFilter: [
                'status_eq' => UserStatusEnum::Active(),
            ],
            pagination: 'page',
            show: 'all',
        );
        $paginatedData = User::applyFilterSortAndPaginate(
            $queries,
            fields: $this->onlys,
        );

        return Inertia::render('Customer/Overview/index', [
            'customers' => UserResponseDTO::transformCollection(
                $paginatedData->data,
                $this->includes,
                onlys: $this->onlys,
            ),
            'pagination' => array_keys_to_camel_case($paginatedData->pagination),
            'dueDays' => get_setting(GlobalSettingEnum::InvoiceDueDays(), 30),
            'creditExpirationDays' => get_setting(GlobalSettingEnum::CreditExpirationDays(), 365),
            'creditRefundTimeWindow' => get_setting(GlobalSettingEnum::CreditRefundTimeWindow(), 72),
        ]);
    }

    /**
     * Display the index view as json.
     */
    public function jsonIndex(): JsonResponse
    {
        $queries = $this->getQueries(
            [
                'roles_name_in' => 'Customer',
            ],
        );
        $paginatedData = User::applyFilterSortAndPaginate($queries);

        return $this->successResponse(
            UserResponseDTO::transformCollection($paginatedData->data),
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUserRequestDTO $request, User $user): RedirectResponse
    {
        // add validation if user not customer
        if (! $user->hasRole('Customer')) {
            throw new NotFoundHttpException();
        }

        $phones = $request->isNotOptional('cellphone') ? explode(' ', $request->cellphone) : [];
        $dialCode = $request->isNotOptional('cellphone') ? str_replace('+', '', $phones[0]) : $user->dial_code;

        DB::transaction(function () use ($request, $user, $phones, $dialCode) {
            $user->update([
                ...$request->toArray(),
                'cellphone' => $request->isNotOptional('cellphone') ? $dialCode.$phones[1] : $user->cellphone,
                'dial_code' => $dialCode,
            ]);
            $user->info->update($request->toArray());

            if ($user->primaryCustomer->membership_type === MembershipTypeEnum::Private()) {
                $user->primaryCustomer->update([
                    'name' => $user->fullname,
                    'identity_number' => $user->identity_number,
                    'email' => $user->email,
                    'phone1' => $user->cellphone,
                    'dial_code' => $dialCode,
                ]);
            }
        });

        return back()->with('success', __('customer updated successfully'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user): RedirectResponse
    {
        // add validation if user not customer
        if (! $user->hasRole('Customer')) {
            throw new NotFoundHttpException();
        }

        $exists = $user->subscriptions()
            ->withTrashed()
            ->where(function (Builder $query) {
                $query->whereHas('schedules', function ($query) {
                    $query->booked();
                })
                    ->orWhereNull('deleted_at');
            })
            ->exists();

        if ($exists) {
            return back()->with('error', __('customer has active schedules or subscriptions'));
        }

        DB::transaction(function () use ($user) {
            $user->status = UserStatusEnum::Deleted();
            $user->save();
            $user->delete();
        });

        return back()->with('success', __('customer deleted successfully'));
    }

    /**
     * Restore the specified resource from storage.
     */
    public function restore(User $user): RedirectResponse
    {
        // add validation if user not customer
        if (! $user->hasRole('Customer')) {
            throw new NotFoundHttpException();
        }

        // check existing user with same cellphone
        $existCellpone = User::whereNotNull('cellphone')
            ->where('cellphone', $user->cellphone)
            ->where('id', '!=', $user->id)
            ->exists();

        if ($existCellpone) {
            return back()->with('error', __('customer phone already in use'));
        }

        DB::transaction(function () use ($user) {
            $user->status = UserStatusEnum::Active();
            $user->save();
            $user->restore();
        });

        return back()->with('success', __('customer restored successfully'));
    }

    /**
     * Get properties of customer.
     */
    public function properties(User $user): JsonResponse
    {
        if (! $user->hasRole(['Customer', 'Company'])) {
            return $this->errorResponse(
                __('not found'),
                HttpResponse::HTTP_NOT_FOUND
            );
        }

        $queries = $this->getQueries(
            [
                'users_id_eq' => $user->id,
            ],
        );
        $paginatedData = Property::applyFilterSortAndPaginate($queries);

        return $this->successResponse(
            PropertyResponseDTO::transformCollection($paginatedData->data, ['address'])
        );
    }

    /**
     *  Get customer addresses
     */
    public function addresses(User $user): JsonResponse
    {
        if (! $user->hasRole(['Customer', 'Company'])) {
            return $this->errorResponse(
                __('not found'),
                HttpResponse::HTTP_NOT_FOUND
            );
        }

        $queries = $this->getQueries(
            [
                'users_id_eq' => $user->id,
            ],
        );
        $paginatedData = Customer::applyFilterSortAndPaginate($queries);

        return $this->successResponse(
            CustomerResponseDTO::transformCollection($paginatedData->data, ['address'])
        );
    }

    /**
     * Get the addresses of private customers.
     */
    public function privateAddresses(): JsonResponse
    {
        $queries = $this->getQueries(
            [
                'membershipType_eq' => MembershipTypeEnum::Private(),
            ],
        );
        $paginatedData = Customer::applyFilterSortAndPaginate($queries);

        return $this->successResponse(
            CustomerResponseDTO::transformCollection($paginatedData->data),
            pagination: $paginatedData->pagination
        );
    }

    /**
     * Get customer credits.
     */
    public function credits(User $user, CreditService $creditService): JsonResponse
    {
        if (! $user->hasRole(['Customer', 'Company'])) {
            return $this->errorResponse(
                __('not found'),
                HttpResponse::HTTP_NOT_FOUND
            );
        }

        $queries = $this->getQueries(
            filter: [
                'userId_eq' => $user->id,
                'remainingAmount_gt' => 0,
                'validUntil_gte' => now()->format(config('data.date_format')),
            ],
            sort: ['valid_until' => 'asc']
        );
        $paginatedData = Credit::applyFilterSortAndPaginate($queries);

        return $this->successResponse(
            CreditResponseDTO::transformCollection($paginatedData->data),
            meta: [
                'totalCredits' => $creditService->getTotal($user->id),
            ],
            pagination: $paginatedData->pagination
        );
    }
}
