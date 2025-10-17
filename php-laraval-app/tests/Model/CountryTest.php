<?php

namespace Tests\Model;

use App\Models\City;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class CountryTest extends TestCase
{
    /** @test */
    public function countriesDatabaseHasExpectedColumns(): void
    {
        $this->assertTrue(
            Schema::hasColumns('countries', [
                'id',
                'code',
                'name',
                'currency',
                'dial_code',
                'flag',
            ]),
        );
    }

    /** @test */
    public function countryHasCities(): void
    {
        $city = City::first();
        $country = $city->country;

        $this->assertIsObject($country->cities);
        $this->assertInstanceOf(City::class, $country->cities->first());
    }
}
