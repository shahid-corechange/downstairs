<?php

namespace App\Http\Controllers\Company;

use App\DTOs\Address\CountryResponseDTO;
use App\DTOs\Property\PropertyTypeResponseDTO;
use App\DTOs\User\UserCompanyCustomerWizardRequestDTO;
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
use App\Models\PropertyType;
use App\Models\User;
use DB;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Str;

class CompanyWizardController extends BaseUserController
{
    /**
     * Display the index view.
     */
    public function index(): Response
    {
        return Inertia::render('Company/Wizard/index', [
            'countries' => $this->getCountries(),
            'propertyTypes' => $this->getPropertyTypes(),
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

    private function getPropertyTypes()
    {
        $onlys = [
            'id',
            'name',
        ];
        $propertyTypes = PropertyType::selectWithRelations($onlys)
            ->whereNot('id', 1)
            ->get();

        return PropertyTypeResponseDTO::collection($propertyTypes)->only(...$onlys);
    }

    /**
     * Store resource in storage.
     * In this case we are creating a customer data for fortnox,
     * address and a property as well.
     */
    public function store(UserCompanyCustomerWizardRequestDTO $request): RedirectResponse
    {
        $data = $request->toArray();
        $result = DB::transaction(function () use ($data) {
            $phones = explode(' ', $data['company_phone']);
            $dialCode = str_replace('+', '', $phones[0]);

            // create user company
            $user = $this->createUser([
                'first_name' => $data['company_name'],
                'last_name' => '',
                'email' => $data['company_email'],
                'cellphone' => $dialCode.$phones[1],
                'dial_code' => $dialCode,
                'password' => Str::random(12),
                'identity_number' => $data['org_number'],
            ], ['Company']);

            $user->info()->create([
                ...$data,
                'marketing' => 0,
            ]);

            // create address
            $address = Address::create($data);

            // create fortnox customer
            $customer = Customer::create([
                'address_id' => $address->id,
                'membership_type' => MembershipTypeEnum::Company(),
                'type' => ContactTypeEnum::Primary(),
                'identity_number' => $user->identity_number,
                'name' => $user->first_name,
                'email' => $user->email,
                'phone1' => $user->cellphone,
                'dial_code' => $user->dial_code,
                'due_days' => $data['due_days'],
                'invoice_method' => $data['invoice_method'],
            ]);

            if (isset($data['customer_meta'])) {
                $customer->saveMeta(array_keys_to_snake_case($data['customer_meta']));
            }

            // create property
            $property = Property::create([
                'address_id' => $address->id,
                'property_type_id' => $data['property_type_id'],
                'membership_type' => MembershipTypeEnum::Company(),
                'square_meter' => $data['square_meter'],
                'key_information' => isset($data['key_information']) ?
                $data['key_information'] : null,
            ]);

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

            $userIds = [$user->id];

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
                    'is_company_contact' => true,
                ], ['Customer']);

                $contact->info()->create([
                    ...$data,
                    'marketing' => 0,
                ]);
                $userIds[] = $contact->id;
            }

            $customer->users()->syncWithoutDetaching($userIds);
            $property->users()->syncWithoutDetaching($userIds);

            // create invoice address
            if (isset($data['invoice_city_id'])) {
                $invoiceCustomer = $this->createInvoiceAddress($data, $user, $userIds);
            }

            return [
                'user' => $user,
                'customer' => $customer,
                'invoiceCustomer' => $invoiceCustomer ?? null,
            ];
        });

        // create customer in fornox
        CreateFortnoxCustomerJob::dispatchAfterResponse($result['customer']);
        if ($result['invoiceCustomer']) {
            CreateFortnoxCustomerJob::dispatchAfterResponse($result['invoiceCustomer']);
        }

        return back()->with('success', __('company created successfully'));
    }

    private function createInvoiceAddress(
        array $data,
        User $user,
        array $userIds,
    ): Customer {
        $address = Address::create([
            'city_id' => $data['invoice_city_id'],
            'address' => $data['invoice_address'],
            'address_2' => isset($data['invoice_address_2']) ?
                $data['invoice_address_2'] : null,
            'postal_code' => $data['invoice_postal_code'],
            'accuracy' => isset($data['invoice_accuracy']) ?
                $data['invoice_accuracy'] : null,
            'latitude' => isset($data['invoice_latitude']) ?
                $data['invoice_latitude'] : null,
            'longitude' => isset($data['invoice_longitude']) ?
                $data['invoice_longitude'] : null,
        ]);

        $customer = Customer::create([
            'address_id' => $address->id,
            'membership_type' => MembershipTypeEnum::Company(),
            'type' => ContactTypeEnum::Invoice(),
            'identity_number' => $data['org_number'],
            'name' => $user->first_name,
            'email' => $user->email,
            'phone1' => $user->cellphone,
            'dial_code' => $user->dial_code,
            'due_days' => $data['due_days'],
            'invoice_method' => $data['invoice_method'],
        ]);

        $customer->users()->syncWithoutDetaching($userIds);

        return $customer;
    }
}
