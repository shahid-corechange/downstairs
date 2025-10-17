<?php

namespace Tests\Model;

use App\Models\PropertyType;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PropertyTypeTest extends TestCase
{
    /** @test */
    public function propertyTypesDatabaseHasExpectedColumns(): void
    {
        $this->assertTrue(
            Schema::hasColumns('property_types', [
                'id',
                'created_at',
                'updated_at',
            ]),
        );
    }

    /** @test */
    public function propertyTypeHasName(): void
    {
        $propertyType = PropertyType::first();

        $this->assertIsString($propertyType->name);
    }

    /** @test */
    public function propertyTypeCanSetName(): void
    {
        $propertyType = PropertyType::first();
        $propertyType->setName('test');

        $this->assertIsString($propertyType->name);
        $this->assertEquals('test', $propertyType->name);
    }

    /** @test */
    public function propertyTypeHasTranslations(): void
    {
        $propertyType = PropertyType::first();

        $this->assertIsObject($propertyType->translations);
    }
}
