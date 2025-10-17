<?php

namespace Tests\Model;

use App\Models\KeyPlace;
use App\Models\Property;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class KeyPlaceTest extends TestCase
{
    /** @test */
    public function keyPlacesDatabaseHasExpectedColumns(): void
    {
        $this->assertTrue(
            Schema::hasColumns('key_places', [
                'id',
                'property_id',
                'created_at',
                'updated_at',
                'deleted_at',
            ]),
        );
    }

    /** @test */
    public function keyPlaceCanCreateKeyPlaceIfFull(): void
    {
        $count = KeyPlace::count();
        KeyPlace::whereNull('property_id')->update(['property_id' => 1]);
        KeyPlace::createKeyPlaceIfFull();
        $newCount = KeyPlace::count();

        $this->assertEquals($count + 1, $newCount);
    }

    /** @test */
    public function keyPlaceHasProperty(): void
    {
        $keyPlace = KeyPlace::where('property_id', '!=', null)->first();

        $this->assertInstanceOf(Property::class, $keyPlace->property);
    }
}
