<?php

namespace App\Http\Controllers\CashierCustomer;

use App\DTOs\User\UpdateUserCashierCompanyCustomerRequestDTO;
use App\DTOs\User\UserCashierCompanyCustomerWizardRequestDTO;
use App\Enums\Discount\CustomerDiscountTypeEnum;
use App\Enums\MembershipTypeEnum;
use App\Enums\User\UserNotificationMethodEnum;
use App\Http\Controllers\User\BaseUserController;
use App\Http\Traits\ResponseTrait;
use App\Jobs\CreateFortnoxCustomerJob;
use App\Jobs\UpdateFortnoxCustomerJob;
use App\Models\Customer;
use App\Models\User;
use App\Services\CashierCustomerService;
use DB;
use Illuminate\Http\RedirectResponse;
use Str;

class CashierCompanyCustomerController extends BaseUserController
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
    public function store(UserCashierCompanyCustomerWizardRequestDTO $request): RedirectResponse
    {
        $data = [
            ...$request->toArray(),
            'membership_type' => MembershipTypeEnum::Company(),
        ];
        $phones = explode(' ', $data['company_phone']);
        $dialCode = str_replace('+', '', $phones[0]);

        [$user, $customer, $invoiceCustomer] = DB::transaction(function () use ($data, $phones, $dialCode) {
            // create user
            $user = $this->createUser([
                'first_name' => $data['company_name'],
                'last_name' => '',
                'email' => $data['company_email'],
                'cellphone' => $dialCode.$phones[1],
                'dial_code' => $dialCode,
                'password' => Str::random(12),
                'identity_number' => $data['org_number'],
            ], ['Company']);

            // create user info
            $user->info()->create([
                ...$data,
                'marketing' => 0,
                'notification_method' => UserNotificationMethodEnum::SMS(),
            ]);

            // create contact person
            if (isset($data['first_name']) && $data['first_name']) {
                $contactPhones = isset($data['cellphone']) && $data['cellphone'] ?
                    explode(' ', $data['cellphone']) : [];
                $contactDialCode = isset($data['cellphone']) && $data['cellphone'] ?
                    str_replace('+', '', $contactPhones[0]) : null;

                $contact = $this->createUser([
                    ...$data,
                    'cellphone' => $contactDialCode ? $contactDialCode.$contactPhones[1] : null,
                    'dial_code' => $contactDialCode,
                    'identity_number' => isset($data['identity_number']) ?
                        $data['identity_number'] : '',
                    'password' => Str::random(12),
                ], ['Customer']);

                $contact->info()->create([
                    ...$data,
                    'marketing' => 0,
                    'notification_method' => UserNotificationMethodEnum::SMS(),
                ]);
            } else {
                $contact = null;
            }

            [$customer, $invoiceCustomer] = $this->service->createCompany($data, $user, $contact);

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

    public function update(UpdateUserCashierCompanyCustomerRequestDTO $request, User $user)
    {
        $data = $request->toArray();
        $phones = explode(' ', $data['phone1']);
        $dialCode = str_replace('+', '', $phones[0]);
        $customer = Customer::find($data['customer_id']);

        $customer = DB::transaction(function () use ($user, $data, $phones, $dialCode, $customer) {
            $user->update([
                'first_name' => $data['name'],
                'last_name' => '',
                'email' => $data['email'],
                'cellphone' => $dialCode.$phones[1],
                'dial_code' => $dialCode,
                'identity_number' => $data['identity_number'],
            ]);

            // update customer
            $customer->update([
                ...$data,
                'phone1' => $dialCode.$phones[1],
                'dial_code' => $dialCode,
            ]);

            // update, create or delete address
            if ($customer->address && $data['email']) {
                $customer->address->update($data);
            } elseif (! $customer->address && $data['email']) {
                $customer->address()->create($data);
            } elseif ($customer->address && ! $data['email']) {
                $customer->address()->delete();
                $customer->update([
                    'address_id' => null,
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
        });

        UpdateFortnoxCustomerJob::dispatchAfterResponse($user, $customer);

        return back()->with([
            'success' => __('customer updated successfully'),
            'successPayload' => [
                'userId' => $user->id,
            ],
        ]);
    }
}
