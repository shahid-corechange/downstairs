<?php

namespace Tests\Model;

use App\Models\Address;
use App\Models\City;
use App\Models\Country;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class CityTest extends TestCase
{
    /** @test */
    public function citiesDatabaseHasExpectedColumns(): void
    {
        $this->assertTrue(
            Schema::hasColumns('cities', [
                'id',
                'country_id',
                'name',
            ]),
        );
    }

    /** @test */
    public function citiesHasAddresses(): void
    {
        $city = City::first();

        $this->assertIsObject($city->addresses);
        $this->assertInstanceOf(Address::class, $city->addresses->first());
    }

    /** @test */
    public function citiesHasCountry(): void
    {
        $city = City::first();

        $this->assertInstanceOf(Country::class, $city->country);
    }
}
