<?php

namespace Tests\Portal\Management;

use App\DTOs\KeyPlace\KeyPlaceResponseDTO;
use App\Models\KeyPlace;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class KeyPlaceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $keyPlace = KeyPlace::whereNotNull('property_id')->first();

        if ($keyPlace && $keyPlace->id !== 1) {
            $propertyId = $keyPlace->property_id;
            $keyPlace->update([
                'property_id' => null,
            ]);
            KeyPlace::find(1)->update([
                'property_id' => $propertyId,
            ]);
        }
    }

    public function testAdminCanAccessKeyPlaces(): void
    {
        $pageSize = config('downstairs.pageSize');
        $count = KeyPlace::count();
        $total = $count > $pageSize ? $pageSize : $count;

        $this->actingAs($this->admin)
            ->get('/keyplaces')
            ->assertInertia(fn (Assert $page) => $page
                ->component('KeyPlace/Overview/index')
                ->has('keyPlaces', $total)
                ->has('keyPlaces.0', fn (Assert $page) => $page
                    ->has('id')
                    ->has('propertyId')
                    ->has('property', fn (Assert $page) => $page
                        ->has('membershipType')
                        ->has('address', fn (Assert $page) => $page
                            ->has('fullAddress')
                            ->etc())
                        ->has('users', fn (Assert $page) => $page
                            ->has('0', fn (Assert $page) => $page
                                ->has('id')
                                ->has('fullname')
                                ->etc())
                            ->etc())))
                ->has('pagination', fn (Assert $page) => $page
                    ->where('total', $count)
                    ->where('size', $pageSize)
                    ->where('currentPage', 1)
                    ->etc()));
    }

    public function testCustomerCanNotAccessKeyPlaces(): void
    {
        $this->actingAs($this->user)
            ->get('/keyplaces')
            ->assertInertia(fn (Assert $page) => $page
                ->component('Error/index')
                ->where('code', '404'));
    }

    public function testCanFilterKeyPlaces(): void
    {
        $data = KeyPlace::first();
        $pageSize = config('downstairs.pageSize');

        $this->actingAs($this->admin)
            ->get("/keyplaces?id.eq={$data->id}")
            ->assertInertia(fn (Assert $page) => $page
                ->component('KeyPlace/Overview/index')
                ->has('keyPlaces', 1)
                ->has('keyPlaces.0', fn (Assert $page) => $page
                    ->where('id', $data->id)
                    ->where('propertyId', $data->property_id)
                    ->has('property', fn (Assert $page) => $page
                        ->where('membershipType', $data->property->membership_type)
                        ->has('address', fn (Assert $page) => $page
                            ->where('fullAddress', $data->property->address->full_address)
                            ->etc())
                        ->has('users', fn (Assert $page) => $page
                            ->has('0', fn (Assert $page) => $page
                                ->has('id')
                                ->has('fullname')
                                ->etc())
                            ->etc())))
                ->has('pagination', fn (Assert $page) => $page
                    ->where('total', 1)
                    ->where('size', $pageSize)
                    ->where('currentPage', 1)
                    ->etc()));
    }

    public function testAdminCanAccessKeyPlacesJson(): void
    {
        $response = $this->actingAs($this->admin)
            ->get('/keyplaces/json');
        $keys = array_keys(
            KeyPlaceResponseDTO::from(KeyPlace::first())->toArray()
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
}
