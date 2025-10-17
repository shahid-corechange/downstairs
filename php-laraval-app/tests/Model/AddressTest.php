<?php

namespace Tests\Model;

use App\Models\Address;
use App\Models\City;
use App\Models\Customer;
use App\Models\Property;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AddressTest extends TestCase
{
    /** @test */
    public function addressesDatabaseHasExpectedColumns(): void
    {
        $this->assertTrue(
            Schema::hasColumns('addresses', [
                'id',
                'city_id',
                'address',
                'address_2',
                'postal_code',
                'area',
                'accuracy',
                'latitude',
                'longitude',
                'created_at',
                'updated_at',
                'deleted_at',
            ]),
        );
    }

    /** @test */
    public function addressHasFullAddress(): void
    {
        $address = Address::first();

        $this->assertIsString($address->full_address);
    }

    /** @test */
    public function addressHasCity(): void
    {
        $address = Address::first();

        $this->assertInstanceOf(City::class, $address->city);
    }

    /** @test */
    public function addressHasProperty(): void
    {
        $address = Address::first();
        $address->property()->create(Property::factory()->make()->toArray());

        $this->assertInstanceOf(Property::class, $address->property);
    }

    /** @test */
    public function addressHasCustomer(): void
    {
        $customer = $this->user->customers()->first();
        /** @var Address $address */
        $address = $customer->address;

        $this->assertInstanceOf(Customer::class, $address->customer);
    }
}
