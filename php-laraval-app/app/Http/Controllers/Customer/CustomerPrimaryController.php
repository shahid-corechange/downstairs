<?php

namespace App\Http\Controllers\Customer;

use App\DTOs\Customer\UpdatePrimaryCustomerRequestDTO;
use App\Http\Controllers\User\BaseUserController;
use App\Jobs\UpdateFortnoxCustomerJob;
use App\Models\Customer;
use App\Models\User;
use DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;

class CustomerPrimaryController extends BaseUserController
{
    /**
     * Update the specified resource in storage.
     */
    public function update(
        User $user,
        Customer $customer,
        UpdatePrimaryCustomerRequestDTO $request,
    ): RedirectResponse {
        $userPhones = $request->isNotOptional('cellphone') ? explode(' ', $request->cellphone) : [];
        $userDialCode = $request->isNotOptional('cellphone') ? str_replace('+', '', $userPhones[0]) : $user->dial_code;
        $phones = $request->isNotOptional('phone1') ? explode(' ', $request->phone1) : [];
        $dialCode = $request->isNotOptional('phone1') ? str_replace('+', '', $phones[0]) : $customer->dial_code;
        $phone1 = $request->isNotOptional('phone1') ? $dialCode.$phones[1] : $customer->phone1;
        $data = $request->toArray();

        DB::transaction(function () use (
            $data,
            $customer,
            $user,
            $userPhones,
            $userDialCode,
            $dialCode,
            $phone1,
            $request,
        ) {
            $customer->address->update($data);
            $customer->update([
                ...$data,
                'phone1' => $phone1,
                'dial_code' => $dialCode,
            ]);

            // Update other customers that reference this customer but different user
            Customer::where('customer_ref_id', $customer->id)
                ->whereDoesntHave('users', function (Builder $query) use ($user) {
                    $query->where('id', $user->id);
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
                ->whereHas('users', function (Builder $query) use ($user) {
                    $query->where('id', $user->id);
                })
                ->update([
                    'membership_type' => $data['membership_type'],
                    'name' => $data['name'],
                    'phone1' => $phone1,
                    'dial_code' => $dialCode,
                ]);

            // Update user
            $user->update([
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'] ?? '',
                'email' => $data['user_email'],
                'cellphone' => $userDialCode.$userPhones[1],
                'dial_code' => $userDialCode,
                'status' => $data['status'],
            ]);

            // Update user info
            if ($user->info) {
                $user->info->update([
                    'language' => $data['language'],
                    'timezone' => $data['timezone'],
                    'notification_method' => $data['notification_method'],
                ]);
            }

            if ($request->isNotOptional('meta')) {
                $customer->purgeMeta();
                $customer->saveMeta($request->meta);
            }
        });

        UpdateFortnoxCustomerJob::dispatchAfterResponse($user, $customer);

        return back()->with('success', __('customer updated successfully'));
    }
}
