<?php

namespace Tests\Model;

use App\Enums\MembershipTypeEnum;
use App\Models\Address;
use App\Models\Property;
use App\Models\PropertyType;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PropertyTest extends TestCase
{
    /** @test */
    public function propertiesDatabaseHasExpectedColumns(): void
    {
        $this->assertTrue(
            Schema::hasColumns('properties', [
                'id',
                'address_id',
                'property_type_id',
                'membership_type',
                'square_meter',
                'key_information',
                'status',
                'created_at',
                'updated_at',
                'deleted_at',
            ]),
        );
    }

    /** @test */
    public function propertyHasKeyDescription(): void
    {
        $property = Property::first();

        if ($property) {
            $this->assertIsString($property->key_description);
        } else {
            $this->assertNull($property);
        }
    }

    /** @test */
    public function propertyHasKeyPlace(): void
    {
        $property = Property::first();

        if ($property->key_place) {
            $this->assertIsString($property->key_place);
        } else {
            $this->assertNull($property->key_place);
        }
    }

    /** @test */
    public function propertyHasAddress(): void
    {
        $property = Property::first();

        if ($property) {
            $this->assertInstanceOf(Address::class, $property->address);
        } else {
            $this->assertNull($property);
        }
    }

    /** @test */
    public function propertyHasCompanyUser(): void
    {
        $property = Property::where('membership_type', MembershipTypeEnum::Company())
            ->first();

        if ($property) {
            $this->assertIsObject($property->companyUser());
        } else {
            $this->assertNull($property);
        }
    }

    /** @test */
    public function propertyHasUsers(): void
    {
        $property = Property::first();

        if ($property) {
            $this->assertIsObject($property->users);
            $this->assertInstanceOf(User::class, $property->users->first());
        } else {
            $this->assertNull($property);
        }
    }

    /** @test */
    public function propertyHasType(): void
    {
        $property = Property::first();

        if ($property) {
            $this->assertInstanceOf(PropertyType::class, $property->type);
        } else {
            $this->assertNull($property);
        }
    }

    /** @test */
    public function propertyHasSubscriptions(): void
    {
        $subscription = Subscription::first();

        if ($subscription) {
            $this->assertIsObject($subscription->property->subscriptions);
            $this->assertInstanceOf(Subscription::class, $subscription->property->subscriptions->first());
        } else {
            $this->assertNull($subscription);
        }
    }
}
