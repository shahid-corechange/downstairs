<?php

namespace App\Services;

use App\Enums\Contact\ContactTypeEnum;
use App\Enums\Discount\CustomerDiscountTypeEnum;
use App\Models\Address;
use App\Models\Customer;
use App\Models\Property;
use App\Models\RutCoApplicant;
use App\Models\User;

class CashierCustomerService
{
    public function createPrivat(array $data, User $user)
    {
        // create address
        $address = $data['city_id'] ? Address::create($data) : null;
        $customer = $this->createCustomer($data, $user, $address);

        if (isset($data['customer_meta'])) {
            $customer->saveMeta(array_keys_to_snake_case($data['customer_meta']));
        }

        $this->createRutCoApplicant($data, $user);
        $this->createProperty($data, $user, $address);

        // create invoice address
        if (isset($data['invoice_city_id'])) {
            $invoiceCustomer = $this->createInvoiceAddress($data, $user);
        } else {
            $invoiceCustomer = null;
        }

        // create discount
        if (isset($data['discount_percentage']) && $data['discount_percentage'] > 0) {
            $user->customerDiscounts()->create([
                'type' => CustomerDiscountTypeEnum::Laundry(),
                'value' => $data['discount_percentage'],
            ]);
        }

        return [
            $customer,
            $invoiceCustomer,
        ];
    }

    /**
     * Create a company customer
     *
     * @param  array  $data
     * @param  User  $user
     * @param  User|null  $contact
     * @return array
     */
    public function createCompany($data, $user, $contact)
    {
        // create address
        $address = $data['city_id'] ? Address::create($data) : null;
        $customer = $this->createCustomer(
            [...$data, 'identity_number' => $user->identity_number],
            $user,
            $address
        );

        // attach contact to customer
        if ($contact) {
            $customer->users()->attach($contact->id);
        }

        if (isset($data['customer_meta'])) {
            $customer->saveMeta(array_keys_to_snake_case($data['customer_meta']));
        }

        $property = $this->createProperty($data, $user, $address);

        // attach contact to property
        if ($contact && $property) {
            $property->users()->attach($contact->id);
        }

        // create invoice address
        if (isset($data['invoice_city_id'])) {
            $invoiceCustomer = $this->createInvoiceAddress($data, $user);
        } else {
            $invoiceCustomer = null;
        }

        // create discount
        if (isset($data['discount_percentage']) && $data['discount_percentage'] > 0) {
            $user->customerDiscounts()->create([
                'type' => CustomerDiscountTypeEnum::Laundry(),
                'value' => $data['discount_percentage'],
            ]);
        }

        return [
            $customer,
            $invoiceCustomer,
        ];
    }

    /**
     * Create a fortnox customer
     *
     * @param  array  $data
     * @param  User  $user
     * @param  Address  $address
     * @return Customer
     */
    private function createCustomer($data, $user, $address)
    {
        // create fortnox customer
        $customer = Customer::create([
            'address_id' => $address ? $address->id : null,
            'membership_type' => $data['membership_type'],
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

        return $customer;
    }

    /**
     * Create a property
     *
     * @param  array  $data
     * @param  User  $user
     * @param  Address  $address
     * @return Property
     */
    private function createProperty($data, $user, $address)
    {
        if ($address) {
            $property = Property::create([
                'address_id' => $address->id,
                'property_type_id' => 1,
                'membership_type' => $data['membership_type'],
                'square_meter' => 0,
            ]);
            $property->users()->attach($user->id);

            return $property;
        }
    }

    /**
     * Create an invoice address
     */
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

        $customer = $this->createCustomer($data, $user, $address);

        return $customer;
    }

    private function createRutCoApplicant(array $data, User $user)
    {
        if ($data['identity_number']) {
            RutCoApplicant::create([
                'user_id' => $user->id,
                'name' => $user->full_name,
                'identity_number' => $data['identity_number'],
                'phone' => $user->cellphone,
                'dial_code' => $user->dial_code,
                'is_enabled' => true,
            ]);
        }
    }
}
