<?php

namespace App\Http\Controllers\Company;

use App\DTOs\Customer\CustomerResponseDTO;
use App\DTOs\Customer\UpdateCompanyCustomerRequestDTO;
use App\DTOs\User\UserResponseDTO;
use App\Enums\Contact\ContactTypeEnum;
use App\Enums\GlobalSetting\GlobalSettingEnum;
use App\Enums\MembershipTypeEnum;
use App\Enums\User\UserStatusEnum;
use App\Http\Controllers\User\BaseUserController;
use App\Http\Traits\ResponseTrait;
use App\Models\Customer;
use App\Models\User;
use DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CompanyAccountController extends BaseUserController
{
    use ResponseTrait;

    /**
     * Additional fields to include in the response.
     */
    private array $includes = [
        'companyUser.info',
    ];

    /**
     * Send only these fields in the response.
     */
    private array $onlys = [
        'id',
        'name',
        'identityNumber',
        'email',
        'companyUser.id',
        'companyUser.fullname',
        'companyUser.email',
        'companyUser.formattedCellphone',
        'companyUser.info.notificationMethod',
        'formattedPhone1',
        'createdAt',
        'updatedAt',
        'deletedAt',
    ];

    /**
     * Display the index view.
     */
    public function index(): Response
    {
        $queries = $this->getQueries(
            filter: [
                'membershipType_eq' => MembershipTypeEnum::Company(),
                'type_eq' => ContactTypeEnum::Primary(),
            ],
            defaultFilter: [
                'deletedAt_eq' => 'null',
            ],
            pagination: 'page',
            show: 'all',
        );
        $paginatedData = Customer::applyFilterSortAndPaginate(
            $queries,
            fields: $this->onlys,
        );

        return Inertia::render('Company/Overview/index', [
            'companies' => CustomerResponseDTO::transformCollection(
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
     * Update the specified resource in storage.
     */
    public function update(UpdateCompanyCustomerRequestDTO $request, Customer $company): RedirectResponse
    {
        // add validation to check if customer is company and primary contact
        if ($company->membership_type !== MembershipTypeEnum::Company() ||
            $company->type !== ContactTypeEnum::Primary()) {
            throw new NotFoundHttpException();
        }

        $phones = $request->isNotOptional('phone1') ? explode(' ', $request->phone1) : [];
        $dialCode = $request->isNotOptional('phone1') ? str_replace('+', '', $phones[0]) : $company->dial_code;

        DB::transaction(function () use ($company, $request, $phones, $dialCode) {
            $company->companyUser->update([
                ...$request->toArray(),
                'first_name' => $request->name,
                'last_name' => '',
                'cellphone' => $request->isNotOptional('phone1') ? $dialCode.$phones[1] : $company->phone1,
                'dial_code' => $dialCode,
            ]);
            $company->companyUser->info->update($request->toArray());

            $company->update([
                ...$request->toArray(),
                'phone1' => $request->isNotOptional('phone1') ? $dialCode.$phones[1] : $company->phone1,
            ]);
        });

        return back()->with('success', __('company updated successfully'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Customer $company): RedirectResponse
    {
        // add validation to check if customer is company and primary contact
        if ($company->membership_type !== MembershipTypeEnum::Company() ||
            $company->type !== ContactTypeEnum::Primary()) {
            throw new NotFoundHttpException();
        }

        $exists = $company->companyUser->subscriptions()
            ->withTrashed()
            ->where(function (Builder $query) {
                $query->whereHas('schedules', function ($query) {
                    $query->booked();
                })
                    ->orWhereNull('deleted_at');
            })
            ->exists();

        if ($exists) {
            return back()->with('error', __('company has active schedules or subscriptions'));
        }

        DB::transaction(function () use ($company) {
            $company->companyUser->update(['status' => UserStatusEnum::Deleted()]);
            $company->companyUser->delete();
            $company->delete();
        });

        return back()->with('success', __('company deleted successfully'));
    }

    /**
     * Restore the specified resource from storage.
     */
    public function restore(Customer $company): RedirectResponse
    {
        // add validation to check if customer is company and primary contact
        if ($company->membership_type !== MembershipTypeEnum::Company() ||
            $company->type !== ContactTypeEnum::Primary()) {
            throw new NotFoundHttpException();
        }

        // check existing user with same cellphone
        $existCellpone = User::whereNotNull('cellphone')
            ->where('cellphone', $company->companyUser->cellphone)
            ->where('id', '!=', $company->companyUser->id)
            ->exists();

        if ($existCellpone) {
            return back()->with('error', __('company phone already in use'));
        }

        DB::transaction(function () use ($company) {
            $company->companyUser->update(['status' => UserStatusEnum::Active()]);
            $company->companyUser->restore();
            $company->restore();
        });

        return back()->with('success', __('company restored successfully'));
    }

    /**
     * Get the addresses of company customers.
     */
    public function addresses(): JsonResponse
    {
        $queries = $this->getQueries(
            [
                'membershipType_eq' => MembershipTypeEnum::Company(),
            ],
        );
        $paginatedData = Customer::applyFilterSortAndPaginate($queries);

        return $this->successResponse(
            CustomerResponseDTO::transformCollection($paginatedData->data),
            pagination: $paginatedData->pagination
        );
    }

    /**
     *  Get the user of companies
     */
    public function companyUsers(): JsonResponse
    {
        $queries = $this->getQueries(
            [
                'roles_name_in' => 'Company',
            ],
        );
        $paginatedData = User::applyFilterSortAndPaginate($queries);

        return $this->successResponse(
            UserResponseDTO::transformCollection($paginatedData->data),
            pagination: $paginatedData->pagination
        );
    }
}
