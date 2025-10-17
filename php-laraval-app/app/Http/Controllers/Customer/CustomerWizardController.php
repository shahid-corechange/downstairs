<?php

namespace App\Http\Controllers\Customer;

use App\DTOs\Address\CountryResponseDTO;
use App\DTOs\User\UserCustomerWizardRequestDTO;
use App\Enums\Contact\ContactTypeEnum;
use App\Enums\GlobalSetting\GlobalSettingEnum;
use App\Enums\MembershipTypeEnum;
use App\Http\Controllers\User\BaseUserController;
use App\Jobs\CreateFortnoxCustomerJob;
use App\Models\Address;
use App\Models\Country;
use App\Models\Customer;
use App\Models\KeyPlace;
use App\Models\Property;
use App\Models\RutCoApplicant;
use App\Models\User;
use DB;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Str;

class CustomerWizardController extends BaseUserController
{
    /**
     * Display the index view.
     */
    public function index(): Response
    {
        return Inertia::render('Customer/Wizard/index', [
            'countries' => $this->getCountries(),
            'dueDays' => get_setting(GlobalSettingEnum::InvoiceDueDays(), 30),
        ]);
    }

    private function getCountries()
    {
        $onlys = [
            'id',
            'name',
        ];
        $countries = Country::selectWithRelations($onlys)->get();

        return CountryResponseDTO::collection($countries)->only(...$onlys);
    }

    /**
     * Store resource in storage.
     * In this case we are creating a customer data for fortnox,
     * address and a property as well.
     */
    public function store(UserCustomerWizardRequestDTO $request): RedirectResponse
    {
        $data = $request->toArray();
        $result = DB::transaction(function () use ($data) {
            $phones = explode(' ', $data['cellphone']);
            $dialCode = str_replace('+', '', $phones[0]);

            // create user
            $user = $this->createUser([
                ...$data,
                'cellphone' => $dialCode.$phones[1],
                'dial_code' => $dialCode,
                'password' => Str::random(12),
            ], ['Customer']);

            // create address
            $address = Address::create($data);

            // create fortnox customer
            $customer = Customer::create([
                'address_id' => $address->id,
                'membership_type' => MembershipTypeEnum::Private(),
                'type' => ContactTypeEnum::Primary(),
                'identity_number' => $data['identity_number'],
                'name' => $user->full_name,
                'email' => $user->email,
                'phone1' => $user->cellphone,
                'dial_code' => $user->dial_code,
                'due_days' => $data['due_days'],
                'invoice_method' => $data['invoice_method'],
            ]);
            $customer->users()->attach($user->id);

            if (isset($data['customer_meta'])) {
                $customer->saveMeta(array_keys_to_snake_case($data['customer_meta']));
            }

            // create RUT co applicant
            RutCoApplicant::create([
                'user_id' => $user->id,
                'name' => $user->full_name,
                'identity_number' => $data['identity_number'],
                'phone' => $user->cellphone,
                'dial_code' => $user->dial_code,
                'is_enabled' => true,
            ]);

            // create property
            $property = Property::create([
                'address_id' => $address->id,
                'property_type_id' => 1,
                'membership_type' => MembershipTypeEnum::Private(),
                'square_meter' => $data['square_meter'],
                'key_information' => isset($data['key_information']) ?
                    $data['key_information'] : null,
            ]);
            $property->users()->attach($user->id);

            if (isset($data['property_meta'])) {
                $property->saveMeta(array_keys_to_snake_case($data['property_meta']));
            }

            // update key place
            if (isset($data['key_information'])
                && isset($data['key_information']['key_place'])
                && $data['key_information']['key_place']) {
                KeyPlace::where('id', $data['key_information']['key_place'])
                    ->update(['property_id' => $property->id]);
                KeyPlace::createKeyPlaceIfFull();
            }

            // create user info
            $user->info()->create([
                ...$data,
                'marketing' => 0,
            ]);

            // create invoice address
            if (isset($data['invoice_city_id'])) {
                $invoiceCustomer = $this->createInvoiceAddress($data, $user);
            }

            return [
                'user' => $user,
                'customer' => $customer,
                'invoiceCustomer' => $invoiceCustomer ?? null,
            ];
        });

        // create customer in fornox using event
        CreateFortnoxCustomerJob::dispatchAfterResponse($result['customer']);
        if ($result['invoiceCustomer']) {
            CreateFortnoxCustomerJob::dispatchAfterResponse($result['invoiceCustomer']);
        }

        return back()->with('success', __('customer created successfully'));
    }

    private function createInvoiceAddress(
        array $data,
        User $user
    ): Customer {
        $address = Address::create([
            'city_id' => $data['invoice_city_id'],
            'address' => $data['invoice_address'],
            'postal_code' => $data['invoice_postal_code'],
            'latitude' => isset($data['invoice_latitude']) ?
                $data['invoice_latitude'] : null,
            'longitude' => isset($data['invoice_longitude']) ?
                $data['invoice_longitude'] : null,
        ]);

        $customer = Customer::create([
            'address_id' => $address->id,
            'membership_type' => MembershipTypeEnum::Private(),
            'type' => ContactTypeEnum::Invoice(),
            'identity_number' => $data['identity_number'],
            'name' => $user->full_name,
            'email' => $user->email,
            'phone1' => $user->cellphone,
            'dial_code' => $user->dial_code,
            'due_days' => $data['due_days'],
            'invoice_method' => $data['invoice_method'],
        ]);

        $customer->users()->attach($user->id);

        return $customer;
    }
}
