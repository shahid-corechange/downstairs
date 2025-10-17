<?php

namespace Tests\Portal\CustomerProperty;

use App\DTOs\Property\PropertyResponseDTO;
use App\Enums\MembershipTypeEnum;
use App\Enums\Property\PropertyTypeEnum;
use App\Models\Address;
use App\Models\City;
use App\Models\Country;
use App\Models\KeyPlace;
use App\Models\Property;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class CustomerPropertyTest extends TestCase
{
    public function testAdminCanAccessCustomerProperties(): void
    {
        $pageSize = config('downstairs.pageSize');
        $count = Property::where(
            'membership_type',
            MembershipTypeEnum::Private()
        )->count();
        $total = $count > $pageSize ? $pageSize : $count;

        $this->actingAs($this->admin)
            ->get('/customers/properties')
            ->assertInertia(fn (Assert $page) => $page
                ->component('Property/Overview/index')
                ->has('properties', $total)
                ->has('properties.0', fn (Assert $page) => $page
                    ->has('id')
                    ->has('squareMeter')
                    ->has('keyDescription')
                    ->has('status')
                    ->etc()
                    ->has('users.0', fn (Assert $page) => $page
                        ->has('id')
                        ->has('fullname')
                        ->etc())
                    ->has('keyInformation', fn (Assert $page) => $page
                        ->has('keyPlace')
                        ->has('frontDoorCode')
                        ->etc())
                    ->has('address', fn (Assert $page) => $page
                        ->has('fullAddress')
                        ->has('id')
                        ->has('cityId')
                        ->has('address')
                        ->has('postalCode')
                        ->has('latitude')
                        ->has('longitude')
                        ->has('city', fn (Assert $page) => $page
                            ->has('name')
                            ->has('countryId')
                            ->has('countryId')
                            ->has('country', fn (Assert $page) => $page
                                ->has('name')))))
                ->has('pagination', fn (Assert $page) => $page
                    ->where('total', $count)
                    ->where('size', $pageSize)
                    ->where('currentPage', 1)
                    ->etc()));
    }

    public function testCustomerCanNotAccessCustomerProperties(): void
    {
        $this->actingAs($this->user)
            ->get('/customers/properties')
            ->assertInertia(fn (Assert $page) => $page
                ->component('Error/index')
                ->where('code', '404'));
    }

    public function testCanFilterCustomerProperties(): void
    {
        $data = Property::where(
            'membership_type',
            MembershipTypeEnum::Private()
        )->first();
        $data->update(['square_meter' => 100.5]);
        $pageSize = config('downstairs.pageSize');

        $this->actingAs($this->admin)
            ->get("/customers/properties?id.eq={$data->id}")
            ->assertInertia(fn (Assert $page) => $page
                ->component('Property/Overview/index')
                ->has('properties', 1)
                ->has('properties.0', fn (Assert $page) => $page
                    ->where('id', $data->id)
                    ->where('squareMeter', $data->square_meter)
                    ->where('keyDescription', $data->key_description)
                    ->where('status', $data->status)
                    ->etc()
                    ->has('users.0', fn (Assert $page) => $page
                        ->where('id', $data->users->first()->id)
                        ->where('fullname', $data->users->first()->fullname)
                        ->etc())
                    ->has('keyInformation', fn (Assert $page) => $page
                        ->has('keyPlace')
                        ->has('frontDoorCode')
                        ->etc())
                    ->has('address', fn (Assert $page) => $page
                        ->where('fullAddress', $data->address->full_address)
                        ->where('id', $data->address->id)
                        ->where('cityId', $data->address->city_id)
                        ->where('address', $data->address->address)
                        ->where('postalCode', $data->address->postal_code)
                        ->where('latitude', $data->address->latitude)
                        ->where('longitude', $data->address->longitude)
                        ->has('city', fn (Assert $page) => $page
                            ->where('name', $data->address->city->name)
                            ->where('countryId', $data->address->city->country_id)
                            ->has('country', fn (Assert $page) => $page
                                ->where('name', $data->address->city->country->name)))))
                ->has('pagination', fn (Assert $page) => $page
                    ->where('total', 1)
                    ->where('size', $pageSize)
                    ->where('currentPage', 1)
                    ->etc()));
    }

    public function testAdminCanAccessCustomerPropertiesJson(): void
    {
        $response = $this->actingAs($this->admin)
            ->get('/customers/properties/json');
        $keys = array_keys(
            PropertyResponseDTO::from(Property::first())->toArray()
        );

        $response->assertStatus(200);

        $response->assertJsonStructure([
            'data' => [
                '*' => $keys,
            ],
            'meta' => [
                'etag',
            ],
        ]);
    }

    public function testCanAccessCustomerPropertyWizard(): void
    {
        $countries = Country::all();
        $customers = User::whereHas('roles', function ($query) {
            $query->where('name', 'Customer');
        })
            ->whereHas('customers', function ($query) {
                $query->where('membership_type', MembershipTypeEnum::Private());
            })
            ->get();

        $this->actingAs($this->admin)
            ->get('/customers/properties/wizard')
            ->assertInertia(fn (Assert $page) => $page
                ->component('Property/Wizard/index')
                ->has('customers', $customers->count())
                ->has('customers.0', fn (Assert $page) => $page
                    ->has('id')
                    ->has('fullname'))
                ->has('countries', $countries->count())
                ->has('countries.0', fn (Assert $page) => $page
                    ->has('id')
                    ->has('name')));
    }

    public function testCanCreateCustomerPropertyFromWizard(): void
    {
        $city = City::first();
        $keyPlace = KeyPlace::whereNull('property_id')->first();
        $data = [
            'userId' => $this->user->id,
            'squareMeter' => 100.00,
            'keyInformation' => [
                'keyPlace' => "$keyPlace->id",
                'information' => 'Test information',
                'alarmCodeOn' => 'Test alarm code on',
                'alarmCodeOff' => 'Test alarm code off',
                'frontDoorCode' => 'Test front door code',
            ],
            // address
            'cityId' => $city->id,
            'address' => 'Test address',
            'postalCode' => '12345',
            'latitude' => 1.0,
            'longitude' => 1.0,
            // meta
            'meta' => [
                'note' => 'Test note',
            ],
        ];

        $this->actingAs($this->admin)
            ->post('/customers/properties/wizard', $data)
            ->assertStatus(302)
            ->assertRedirect()
            ->assertSessionHas('success', __('property created successfully'));

        $address = Address::where('address', $data['address'])->first();

        $this->assertDatabaseHas('addresses', [
            'id' => $address->id,
            'city_id' => $data['cityId'],
            'address' => $data['address'],
            'postal_code' => $data['postalCode'],
            'latitude' => $data['latitude'],
            'longitude' => $data['longitude'],
        ]);

        $this->assertDatabaseHas('properties', [
            'address_id' => $address->id,
            'property_type_id' => 1,
            'membership_type' => MembershipTypeEnum::Private(),
            'square_meter' => $data['squareMeter'],
        ]);

        /** @var array $keys */
        $keys = $data['keyInformation'];
        $keyInformation = json_encode(array_keys_to_snake_case($keys), true);
        $property = Property::where('address_id', $address->id)->first();
        $this->assertEquals($keyInformation, json_encode($property->key_information));

        $this->assertDatabaseHas('key_places', [
            'id' => $keys['keyPlace'],
            'property_id' => $property->id,
        ]);

        $this->assertDatabaseHas('meta', [
            'metable_type' => Property::class,
            'metable_id' => $property->id,
            'key' => 'note',
            'value' => $data['meta']['note'],
        ]);
    }

    public function testCanUpdateCustomerProperty(): void
    {
        $keyPlace = KeyPlace::whereNull('property_id')->first();
        $property = $this->user->properties()->first();
        $oldAddressId = $property->address_id;
        $data = [
            'userId' => $this->user->id,
            'type' => PropertyTypeEnum::House(),
            'squareMeter' => 100.00,
            'keyInformation' => [
                'keyPlace' => "$keyPlace->id",
                'information' => 'Test information',
                'alarmCodeOn' => 'Test alarm code on',
                'alarmCodeOff' => 'Test alarm code off',
                'frontDoorCode' => 'Test front door code',
            ],
            // meta
            'meta' => [
                'note' => 'Test note',
            ],
            // address
            'address' => [
                'cityId' => config('downstairs.test.city_id'),
                'address' => 'Lundströmgatan 1',
                'postalCode' => '72232',
                'latitude' => 5.590734,
                'longitude' => 5.70789,
            ],
        ];

        $this->actingAs($this->admin)
            ->patch("/customers/properties/$property->id", $data)
            ->assertStatus(302)
            ->assertRedirect()
            ->assertSessionHas('success', __('property updated successfully'));

        $property->refresh();
        $this->assertDatabaseHas('properties', [
            'address_id' => $property->address->id,
            'membership_type' => MembershipTypeEnum::Private(),
            'square_meter' => $data['squareMeter'],
        ]);

        /** @var array $keys */
        $keys = $data['keyInformation'];
        $keyInformation = json_encode(array_keys_to_snake_case($keys), true);
        $this->assertEquals($keyInformation, json_encode($property->key_information));

        $this->assertDatabaseHas('key_places', [
            'id' => $keys['keyPlace'],
            'property_id' => $property->id,
        ]);

        $this->assertDatabaseHas('meta', [
            'metable_type' => Property::class,
            'metable_id' => $property->id,
            'key' => 'note',
            'value' => $data['meta']['note'],
        ]);

        $address = Address::find($oldAddressId);
        $this->assertNotEquals($data['address']['address'], $address->address);
        $this->assertNotEquals($data['address']['postalCode'], $address->postal_code);
        $this->assertNotEquals($data['address']['latitude'], $address->latitude);
        $this->assertNotEquals($data['address']['longitude'], $address->longitude);

        $this->assertDatabaseHas('addresses', [
            'city_id' => $data['address']['cityId'],
            'address' => $data['address']['address'],
            'postal_code' => $data['address']['postalCode'],
            'latitude' => $data['address']['latitude'],
            'longitude' => $data['address']['longitude'],
        ]);
    }

    public function testCanDeleteCustomerProperty(): void
    {
        $address = Address::first();
        $property = Property::factory()
            ->assignAddress($address->id)
            ->setMembershipType(MembershipTypeEnum::Private())
            ->hasAttached($this->user)
            ->create();
        $propertyId = $property->id;
        $property->subscriptions()
            ->withTrashed()
            ->where(function (Builder $query) {
                $query->whereHas('scheduleCleanings', function ($query) {
                    $query->active();
                })
                    ->orWhereNull('deleted_at');
            })
            ->forceDelete();

        $this->actingAs($this->admin)
            ->delete("/customers/properties/{$propertyId}")
            ->assertStatus(302)
            ->assertRedirect()
            ->assertSessionHas('success', __('property deleted successfully'));

        $this->assertSoftDeleted('properties', [
            'id' => $propertyId,
        ]);
    }

    public function testCanNotDeleteCustomerProperty(): void
    {
        $subscription = Subscription::first();

        $this->actingAs($this->admin)
            ->delete("/customers/properties/{$subscription->property_id}")
            ->assertStatus(302)
            ->assertRedirect()
            ->assertSessionHas('error', __('property still used in schedules or subscriptions'));
    }

    public function testCanRestoreCustomerProperty(): void
    {
        $property = Property::first();
        $property->delete();

        $this->actingAs($this->admin)
            ->post("/customers/properties/{$property->id}/restore")
            ->assertStatus(302)
            ->assertRedirect()
            ->assertSessionHas('success', __('property restored successfully'));
    }
}
