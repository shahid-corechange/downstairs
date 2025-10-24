<?php

namespace App\Http\Controllers\CashierCustomer;

use App\DTOs\User\UpdateUserCashierPrivateCustomerRequestDTO;
use App\DTOs\User\UserCashierPrivateCustomerWizardRequestDTO;
use App\Enums\Discount\CustomerDiscountTypeEnum;
use App\Enums\MembershipTypeEnum;
use App\Http\Controllers\User\BaseUserController;
use App\Http\Traits\ResponseTrait;
use App\Jobs\CreateFortnoxCustomerJob;
use App\Jobs\UpdateFortnoxCustomerJob;
use App\Models\Address;
use App\Models\Customer;
use App\Models\User;
use App\Services\CashierCustomerService;
use DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Str;

class CashierPrivateCustomerController extends BaseUserController
{
    use ResponseTrait;

    public function __construct(
        private CashierCustomerService $service
    ) {
    }

    /**
     * Store a new customer
     *
     * Direct customer only saves
     * - Name
     * - Phone Number
     * - Identity Number
     */
    public function store(UserCashierPrivateCustomerWizardRequestDTO $request): RedirectResponse
    {
        $data = [
            ...$request->toArray(),
            'membership_type' => MembershipTypeEnum::Private(),
            'last_name' => $request->last_name ?? '',
        ];
        $phones = explode(' ', $data['cellphone']);
        $dialCode = str_replace('+', '', $phones[0]);
        $data['identity_number'] = $data['identity_number'] ?? '';

        [$user, $customer, $invoiceCustomer] = DB::transaction(function () use ($data, $phones, $dialCode) {
            // create user
            $user = $this->createUser([
                ...$data,
                'cellphone' => $dialCode.$phones[1],
                'dial_code' => $dialCode,
                'password' => Str::random(12),
            ], ['Customer']);

            // create user info
            $user->info()->create([
                ...$data,
                'marketing' => 0,
            ]);

            [$customer, $invoiceCustomer] = $this->service->createPrivat($data, $user);

            return [$user, $customer, $invoiceCustomer];
        });

        // create customer in fornox using event
        if ($customer) {
            CreateFortnoxCustomerJob::dispatchAfterResponse($customer);
        }

        // create invoice customer in fornox using event
        if ($invoiceCustomer) {
            CreateFortnoxCustomerJob::dispatchAfterResponse($invoiceCustomer);
        }

        return back()->with([
            'success' => __('customer created successfully'),
            'successPayload' => [
                'userId' => $user->id,
            ],
        ]);
    }

    public function update(UpdateUserCashierPrivateCustomerRequestDTO $request, User $user)
    {
        $data = $request->toArray();
        $phones = explode(' ', $data['phone1']);
        $dialCode = str_replace('+', '', $phones[0]);
        $userPhones = explode(' ', $data['cellphone']);
        $userDialCode = str_replace('+', '', $userPhones[0]);
        $customer = Customer::find($data['customer_id']);

        $customer = DB::transaction(
            function () use (
                $user,
                $data,
                $phones,
                $dialCode,
                $customer,
                $userPhones,
                $userDialCode
            ) {
                // update customer
                $customer->update([
                    ...$data,
                    'phone1' => $dialCode.$phones[1],
                    'dial_code' => $dialCode,
                ]);

                // update or create address
                if ($customer->address && $data['city_id']) {
                    $customer->address->update($data);
                } elseif (! $customer->address_id && $data['city_id']) {
                    $address = Address::create($data);
                    $customer->update(['address_id' => $address->id]);
                }

                // Update other customers that reference this customer but different user
                Customer::where('customer_ref_id', $customer->id)
                    ->whereDoesntHave('users', function (Builder $query) use ($user) {
                        $query->where('id', $user->id);
                    })
                    ->update([
                        'membership_type' => $data['membership_type'],
                        'name' => $data['name'],
                        'email' => $data['email'],
                        'phone1' => $dialCode.$phones[1],
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
                        'phone1' => $dialCode.$phones[1],
                        'dial_code' => $dialCode,
                    ]);

                $user->update([
                    'first_name' => $data['first_name'],
                    'last_name' => $data['last_name'] ?? '',
                    'email' => $data['user_email'],
                    'cellphone' => $userDialCode.$userPhones[1],
                    'dial_code' => $userDialCode,
                    'status' => $data['status'],
                ]);

                // Update user info (including language, timezone, and notification_method)
                if ($user->info) {
                    $user->info->update([
                        'language' => $data['language'],
                        'timezone' => $data['timezone'],
                        'notification_method' => $data['notification_method'],
                    ]);
                }

                // update or create discount
                if (isset($data['discount_id'])) {
                    $user->customerDiscounts()
                        ->where('id', $data['discount_id'])
                        ->update([
                            'value' => $data['discount_percentage'],
                        ]);
                } elseif (isset($data['discount_percentage']) && $data['discount_percentage'] > 0) {
                    $user->customerDiscounts()->create([
                        'type' => CustomerDiscountTypeEnum::Laundry(),
                        'value' => $data['discount_percentage'],
                    ]);
                }

                return $customer;
            }
        );

        UpdateFortnoxCustomerJob::dispatchAfterResponse($user, $customer);

        return back()->with([
            'success' => __('customer updated successfully'),
            'successPayload' => [
                'userId' => $user->id,
            ],
        ]);
    }
}
