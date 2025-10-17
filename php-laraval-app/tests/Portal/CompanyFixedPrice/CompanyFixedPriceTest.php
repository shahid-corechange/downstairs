<?php

namespace Tests\Portal\CompanyFixedPrice;

use App\DTOs\FixedPrice\FixedPriceResponseDTO;
use App\Enums\FixedPrice\FixedPriceRowTypeEnum;
use App\Enums\VatNumbersEnum;
use App\Models\FixedPrice;
use App\Models\FixedPriceRow;
use App\Models\Subscription;
use Illuminate\Database\Eloquent\Builder;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class CompanyFixedPriceTest extends TestCase
{
    public function testAdminCanAccessCompanyFixedPrices(): void
    {
        $pageSize = config('downstairs.pageSize');
        $fixedPrices = FixedPrice::whereHas('user', function (Builder $query) {
            $query->whereHas('roles', function (Builder $query) {
                $query->where('name', 'Company');
            });
        })->get();

        foreach ($fixedPrices as $fixedPrice) {
            $fixedPrice->update([
                'start_date' => null,
                'end_date' => null,
            ]);
        }

        $count = $fixedPrices->where('is_active', true)->count();
        $total = $count > $pageSize ? $pageSize : $count;

        $this->actingAs($this->admin)
            ->get('/companies/fixedprices')
            ->assertInertia(fn (Assert $page) => $page
                ->component('Company/FixedPrice/index')
                ->has('fixedPrices', $total)
                ->has('fixedPrices.0', fn (Assert $page) => $page
                    ->has('id')
                    ->has('userId')
                    ->has('isPerOrder')
                    ->etc()
                    ->has('user', fn (Assert $page) => $page
                        ->has('fullname')))
                ->has('pagination', fn (Assert $page) => $page
                    ->where('total', $count)
                    ->where('size', $pageSize)
                    ->where('currentPage', 1)
                    ->etc()));
    }

    public function testCustomerCanNotAccessCompanyFixedPrices(): void
    {
        $this->actingAs($this->user)
            ->get('/companies/fixedprices')
            ->assertInertia(fn (Assert $page) => $page
                ->component('Error/index')
                ->where('code', '404'));
    }

    public function testCanFilterCompanyFixedPrices(): void
    {
        $data = FixedPrice::whereHas('user', function (Builder $query) {
            $query->whereHas('roles', function (Builder $query) {
                $query->where('name', 'Company');
            });
        })->first();
        $pageSize = config('downstairs.pageSize');

        $data->update([
            'start_date' => null,
            'end_date' => null,
        ]);

        $this->actingAs($this->admin)
            ->get("/companies/fixedprices?id.eq={$data->id}")
            ->assertInertia(fn (Assert $page) => $page
                ->component('Company/FixedPrice/index')
                ->has('fixedPrices', 1)
                ->has('fixedPrices.0', fn (Assert $page) => $page
                    ->where('id', $data->id)
                    ->where('userId', $data->user_id)
                    ->where('isPerOrder', $data->is_per_order)
                    ->etc()
                    ->has('user', fn (Assert $page) => $page
                        ->where('fullname', $data->user->fullname)))
                ->has('pagination', fn (Assert $page) => $page
                    ->where('total', 1)
                    ->where('size', $pageSize)
                    ->where('currentPage', 1)
                    ->etc()));
    }

    public function testAdminCanAccessCompanyFixedPricesJson(): void
    {
        $response = $this->actingAs($this->admin)
            ->get('/companies/fixedprices/json');
        $keys = array_keys(
            FixedPriceResponseDTO::fromModel(FixedPrice::first())->toArray()
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

    public function testAdminCanAccessCompanyFixedPriceJson(): void
    {
        $data = FixedPrice::whereHas('user', function (Builder $query) {
            $query->whereHas('roles', function (Builder $query) {
                $query->where('name', 'Company');
            });
        })->first();
        $response = $this->actingAs($this->admin)
            ->get("/companies/fixedprices/{$data->id}/json");
        $keys = array_keys(
            FixedPriceResponseDTO::from($data)->toArray()
        );

        $response->assertStatus(200);

        $response->assertJsonStructure([
            'data' => $keys,
            'meta' => [
                'etag',
            ],
        ]);
    }

    public function testCanCreateCompanyFixedPrice(): void
    {
        FixedPrice::whereNull('deleted_at')->forceDelete();
        FixedPriceRow::whereNotNull('id')->delete();
        $subscription = Subscription::first();
        $subscription->update([
            'fixed_price_id' => null,
        ]);

        $data = [
            'userId' => 1,
            'isPerOrder' => true,
            'subscriptionIds' => [$subscription->id],
            'rows' => [
                [
                    'type' => FixedPriceRowTypeEnum::Service(),
                    'quantity' => 1,
                    'price' => 300,
                    'vatGroup' => VatNumbersEnum::TwentyFive(),
                ],
            ],
            'meta' => [
                'includeLaundry' => false,
            ],
        ];

        $this->actingAs($this->admin)
            ->post('/companies/fixedprices', $data)
            ->assertStatus(302)
            ->assertRedirect()
            ->assertSessionHas('success', __('fixed price created successfully'));

        $this->assertDatabaseHas('fixed_prices', [
            'user_id' => $data['userId'],
            'is_per_order' => $data['isPerOrder'],
        ]);

        $this->assertDatabaseHas('fixed_price_rows', [
            'type' => FixedPriceRowTypeEnum::Service(),
            'quantity' => 1,
            'price' => 300 / 1.25,
            'vat_group' => VatNumbersEnum::TwentyFive(),
        ]);
    }

    public function testCanCreateCompanyFixedPriceIncludeLaundry(): void
    {
        FixedPrice::whereNull('deleted_at')->forceDelete();
        FixedPriceRow::whereNotNull('id')->delete();
        $subscription = Subscription::first();
        $subscription->update([
            'fixed_price_id' => null,
        ]);

        $data = [
            'userId' => 1,
            'isPerOrder' => false,
            'subscriptionIds' => [$subscription->id],
            'rows' => [
                [
                    'type' => FixedPriceRowTypeEnum::Service(),
                    'quantity' => 1,
                    'price' => 300,
                    'vatGroup' => VatNumbersEnum::TwentyFive(),
                ],
            ],
            'meta' => [
                'includeLaundry' => true,
            ],
        ];

        $this->actingAs($this->admin)
            ->post('/companies/fixedprices', $data)
            ->assertStatus(302)
            ->assertRedirect()
            ->assertSessionHas('success', __('fixed price created successfully'));

        $this->assertDatabaseHas('fixed_prices', [
            'user_id' => $data['userId'],
            'is_per_order' => $data['isPerOrder'],
        ]);

        $this->assertDatabaseHas('fixed_price_rows', [
            'type' => FixedPriceRowTypeEnum::Service(),
            'quantity' => 1,
            'price' => 300 / 1.25,
            'vat_group' => VatNumbersEnum::TwentyFive(),
        ]);

        $this->assertDatabaseHas('meta', [
            'metable_type' => FixedPrice::class,
            'key' => 'include_laundry',
            'value' => '1',
            'type' => 'boolean',
        ]);
    }

    public function testCanNotCreateCompanyFixedPriceIncludeLaundryIfPerOrder(): void
    {
        FixedPrice::whereNull('deleted_at')->forceDelete();
        FixedPriceRow::whereNotNull('id')->delete();
        $subscription = Subscription::first();
        $subscription->update([
            'fixed_price_id' => null,
        ]);

        $data = [
            'userId' => 1,
            'isPerOrder' => true,
            'subscriptionIds' => [$subscription->id],
            'rows' => [
                [
                    'type' => FixedPriceRowTypeEnum::Service(),
                    'quantity' => 1,
                    'price' => 300,
                    'vatGroup' => 25,
                ],
            ],
            'meta' => [
                'includeLaundry' => true,
            ],
        ];

        $this->actingAs($this->admin)
            ->post('/companies/fixedprices', $data)
            ->assertStatus(302)
            ->assertRedirect()
            ->assertSessionHas('error', __('laundry must be applied in monthly'));
    }

    public function testCanNotCreateCompanyFixedPriceIncludeLaundryIfExists(): void
    {
        FixedPrice::whereNull('deleted_at')->forceDelete();
        FixedPriceRow::whereNotNull('id')->delete();
        $subscription = Subscription::first();
        $subscription->update([
            'fixed_price_id' => null,
        ]);

        $data = [
            'userId' => 1,
            'isPerOrder' => false,
            'subscriptionIds' => [$subscription->id],
            'rows' => [
                [
                    'type' => FixedPriceRowTypeEnum::Service(),
                    'quantity' => 1,
                    'price' => 300,
                    'vatGroup' => 25,
                ],
            ],
            'meta' => [
                'includeLaundry' => true,
            ],
        ];

        $fixedPrice = FixedPrice::create([
            'user_id' => 1,
            'is_per_order' => false,
        ]);

        $fixedPrice->saveMeta([
            'include_laundry' => true,
        ]);

        $this->actingAs($this->admin)
            ->post('/companies/fixedprices', $data)
            ->assertStatus(302)
            ->assertRedirect()
            ->assertSessionHas('error', __('laundry fixed price already exists'));
    }

    public function testCanNotCreateCompanyFixedPriceIfAlreadyHaveFixedPrice(): void
    {
        $count = Subscription::whereIn('id', [1])
            ->whereNotNull('fixed_price_id')
            ->count();
        $data = [
            'userId' => 1,
            'isPerOrder' => false,
            'subscriptionIds' => [1],
            'rows' => [
                [
                    'type' => FixedPriceRowTypeEnum::Service(),
                    'quantity' => 1,
                    'price' => 300,
                    'vatGroup' => 25,
                ],
            ],
            'meta' => [
                'includeLaundry' => false,
            ],
        ];

        $this->actingAs($this->admin)
            ->post('/companies/fixedprices', $data)
            ->assertStatus(302)
            ->assertRedirect()
            ->assertSessionHas(
                'error',
                __('there are subscription that already have fixed price', ['count' => $count])
            );
    }

    public function testCanNotCreateCompanyFixedPriceIfRowNotUnique(): void
    {
        FixedPrice::whereNull('deleted_at')->forceDelete();
        FixedPriceRow::whereNotNull('id')->delete();
        $subscription = Subscription::first();
        $subscription->update([
            'fixed_price_id' => null,
        ]);

        $data = [
            'userId' => 1,
            'isPerOrder' => false,
            'subscriptionIds' => [$subscription->id],
            'rows' => [
                [
                    'type' => FixedPriceRowTypeEnum::Service(),
                    'quantity' => 1,
                    'price' => 300,
                    'vatGroup' => VatNumbersEnum::TwentyFive(),
                ],
                [
                    'type' => FixedPriceRowTypeEnum::Service(),
                    'quantity' => 1,
                    'price' => 100,
                    'vatGroup' => VatNumbersEnum::TwentyFive(),
                ],
            ],
            'meta' => [
                'includeLaundry' => false,
            ],
        ];

        $this->actingAs($this->admin)
            ->post('/companies/fixedprices', $data)
            ->assertStatus(302)
            ->assertRedirect()
            ->assertSessionHas('error', __('fixed price row type must be unique'));
    }

    public function testCanUpdateCompanyFixedPrice(): void
    {
        $fixedPrice = FixedPrice::first();
        $subscription = Subscription::first();
        $subscription->update([
            'fixed_price_id' => null,
        ]);

        $data = [
            'isPerOrder' => false,
            'subscriptionIds' => [$subscription->id],
            'meta' => [
                'includeLaundry' => false,
            ],
        ];

        $this->actingAs($this->admin)
            ->patch("/companies/fixedprices/{$fixedPrice->id}", $data)
            ->assertStatus(302)
            ->assertRedirect()
            ->assertSessionHas('success', __('fixed price updated successfully'));

        $this->assertDatabaseHas('fixed_prices', [
            'id' => $fixedPrice->id,
            'is_per_order' => $data['isPerOrder'],
        ]);

        $this->assertDatabaseHas('meta', [
            'metable_type' => FixedPrice::class,
            'key' => 'include_laundry',
            'value' => '0',
            'type' => 'boolean',
        ]);
    }

    public function testCanNotUpdateCompanyFixedPriceIncludeLaundryIfPerOrder(): void
    {
        $fixedPrice = FixedPrice::first();
        $subscription = Subscription::first();
        $subscription->update([
            'fixed_price_id' => null,
        ]);

        $data = [
            'isPerOrder' => true,
            'subscriptionIds' => [$subscription->id],
            'meta' => [
                'includeLaundry' => true,
            ],
        ];

        $this->actingAs($this->admin)
            ->patch("/companies/fixedprices/{$fixedPrice->id}", $data)
            ->assertStatus(302)
            ->assertRedirect()
            ->assertSessionHas(
                'error',
                __('laundry must be applied in monthly')
            );
    }

    public function testCanNotUpdateCompanyFixedPriceIncludeLaundryIfExists(): void
    {
        $fixedPrice = FixedPrice::first();
        $subscription = Subscription::first();
        $subscription->update([
            'fixed_price_id' => null,
        ]);

        $data = [
            'isPerOrder' => false,
            'subscriptionIds' => [$subscription->id],
            'meta' => [
                'includeLaundry' => true,
            ],
        ];

        $fixedPrice2 = FixedPrice::create([
            'user_id' => $fixedPrice->user_id,
            'is_per_order' => false,
        ]);

        $fixedPrice2->saveMeta([
            'include_laundry' => true,
        ]);

        $this->actingAs($this->admin)
            ->patch("/companies/fixedprices/{$fixedPrice->id}", $data)
            ->assertStatus(302)
            ->assertRedirect()
            ->assertSessionHas(
                'error',
                __('laundry fixed price already exists')
            );
    }

    public function testCanNotUpdateCompanyFixedPriceIfAlreadyHaveFixedPrice(): void
    {
        $fixedPrice = FixedPrice::first();
        $countSubscription = Subscription::whereIn('id', [1, 2, 3, 4])
            ->where('fixed_price_id', '!=', $fixedPrice->id)
            ->whereNotNull('fixed_price_id')
            ->count();

        $data = [
            'isPerOrder' => false,
            'subscriptionIds' => [1, 2, 3, 4],
            'meta' => [
                'includeLaundry' => false,
            ],
        ];

        $this->actingAs($this->admin)
            ->patch("/companies/fixedprices/{$fixedPrice->id}", $data)
            ->assertStatus(302)
            ->assertRedirect()
            ->assertSessionHas(
                'error',
                __(
                    'there are subscription that already have fixed price',
                    ['count' => $countSubscription]
                )
            );
    }

    public function testCanDeleteCompanyFixedPrice(): void
    {
        $this->actingAs($this->admin)
            ->delete('/companies/fixedprices/1')
            ->assertStatus(302)
            ->assertRedirect()
            ->assertSessionHas('success', __('fixed price deleted successfully'));

        $this->assertSoftDeleted('fixed_prices', [
            'id' => 1,
        ]);
    }

    public function testCanRestoreCompanyFixedPrice(): void
    {
        $fixedPrice = FixedPrice::first();
        $fixedPrice->delete();

        $this->actingAs($this->admin)
            ->post("/companies/fixedprices/{$fixedPrice->id}/restore")
            ->assertStatus(302)
            ->assertRedirect()
            ->assertSessionHas('success', __('fixed price restored successfully'));

        $this->assertDatabaseHas('fixed_prices', [
            'id' => $fixedPrice->id,
            'deleted_at' => null,
        ]);
    }

    public function testCanRestoreCompanyFixedPriceIncludeLaundry(): void
    {
        FixedPrice::whereNull('deleted_at')->forceDelete();
        FixedPriceRow::whereNotNull('id')->delete();

        $fixedPrice = FixedPrice::create([
            'user_id' => 1,
            'is_per_order' => false,
        ]);

        $fixedPrice->saveMeta([
            'include_laundry' => true,
        ]);
        $fixedPrice->delete();

        $this->actingAs($this->admin)
            ->post("/companies/fixedprices/{$fixedPrice->id}/restore")
            ->assertStatus(302)
            ->assertRedirect()
            ->assertSessionHas('success', __('fixed price restored successfully'));

        $this->assertDatabaseHas('fixed_prices', [
            'id' => $fixedPrice->id,
            'deleted_at' => null,
        ]);

        $this->assertDatabaseHas('meta', [
            'metable_type' => FixedPrice::class,
            'metable_id' => $fixedPrice->id,
            'key' => 'include_laundry',
            'value' => '1',
            'type' => 'boolean',
        ]);
    }

    public function testCanNotRestoreCompanyFixedPriceIncludeLaundryIfExists(): void
    {
        FixedPrice::whereNull('deleted_at')->forceDelete();
        FixedPriceRow::whereNotNull('id')->delete();

        $fixedPrice = FixedPrice::create([
            'user_id' => 1,
            'is_per_order' => false,
        ]);

        $fixedPrice->saveMeta([
            'include_laundry' => true,
        ]);
        $fixedPrice->delete();

        $fixedPrice2 = FixedPrice::create([
            'user_id' => 1,
            'is_per_order' => false,
        ]);

        $fixedPrice2->saveMeta([
            'include_laundry' => true,
        ]);

        $this->actingAs($this->admin)
            ->post("/companies/fixedprices/{$fixedPrice->id}/restore")
            ->assertStatus(302)
            ->assertRedirect()
            ->assertSessionHas('error', __('laundry fixed price already exists'));
    }
}
