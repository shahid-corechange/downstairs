<?php

namespace App\Http\Controllers\Company;

use App\DTOs\Customer\UpdatePrimaryCustomerRequestDTO;
use App\Enums\Contact\ContactTypeEnum;
use App\Enums\MembershipTypeEnum;
use App\Http\Controllers\User\BaseUserController;
use App\Jobs\UpdateFortnoxCustomerJob;
use App\Models\Customer;
use DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CompanyPrimaryController extends BaseUserController
{
    /**
     * Update the specified resource in storage.
     */
    public function update(
        Customer $company,
        Customer $customer,
        UpdatePrimaryCustomerRequestDTO $request,
    ): RedirectResponse {
        if ($company->membership_type !== MembershipTypeEnum::Company() ||
            $company->type !== ContactTypeEnum::Primary() ||
            ! $company->companyUser?->customers->contains($customer)) {
            throw new NotFoundHttpException();
        }

        $userPhones = $request->isNotOptional('cellphone') ? explode(' ', $request->cellphone) : [];
        $userDialCode = $request->isNotOptional('cellphone') ?
            str_replace('+', '', $userPhones[0]) : $company->companyUser->dial_code;
        $phones = $request->isNotOptional('phone1') ? explode(' ', $request->phone1) : [];
        $dialCode = $request->isNotOptional('phone1') ?
            str_replace('+', '', $phones[0]) : $customer->dial_code;
        $phone1 = $request->isNotOptional('phone1') ? $dialCode.$phones[1] : $customer->phone1;
        $data = $request->toArray();

        DB::transaction(function () use (
            $data,
            $request,
            $customer,
            $company,
            $userPhones,
            $userDialCode,
            $dialCode,
            $phone1,
        ) {
            $customer->address->update($data);
            $customer->update([
                ...$data,
                'phone1' => $phone1,
                'dial_code' => $dialCode,
            ]);

            // Update other customers that reference this customer but different user
            Customer::where('customer_ref_id', $customer->id)
                ->whereDoesntHave('users', function (Builder $query) use ($company) {
                    $query->where('id', $company->companyUser->id);
                })
                ->update([
                    'membership_type' => $data['membership_type'],
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'phone1' => $phone1,
                    'dial_code' => $dialCode,
                ]);
            // Update other customers that share the same user
            Customer::where('customer_ref_id', $customer->id)
                ->WhereHas('users', function (Builder $query) use ($company) {
                    $query->where('id', $company->companyUser->id);
                })
                ->update([
                    'membership_type' => $data['membership_type'],
                    'name' => $data['name'],
                    'phone1' => $phone1,
                    'dial_code' => $dialCode,
                ]);

            $user = $company->companyUser;

            $user->update([
                'first_name' => $data['first_name'],
                'last_name' => '',
                'email' => $data['user_email'],
                'cellphone' => $userDialCode.$userPhones[1],
                'dial_code' => $userDialCode,
            ]);

            if ($user->info) {
                $user->info->update([
                    'notification_method' => $data['notification_method'],
                ]);
            }

            if ($request->isNotOptional('meta')) {
                $customer->purgeMeta();
                $customer->saveMeta($request->meta);
            }
        });

        UpdateFortnoxCustomerJob::dispatchAfterResponse($company->companyUser, $customer);

        return back()->with('success', __('company updated successfully'));
    }
}
