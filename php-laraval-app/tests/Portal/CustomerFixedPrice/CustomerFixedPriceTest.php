<?php

namespace Tests\Portal\CustomerFixedPrice;

use App\DTOs\FixedPrice\FixedPriceResponseDTO;
use App\Enums\FixedPrice\FixedPriceRowTypeEnum;
use App\Enums\Invoice\InvoiceStatusEnum;
use App\Enums\Invoice\InvoiceTypeEnum;
use App\Enums\VatNumbersEnum;
use App\Models\Customer;
use App\Models\FixedPrice;
use App\Models\Invoice;
use App\Models\Subscription;
use Illuminate\Database\Eloquent\Builder;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class CustomerFixedPriceTest extends TestCase
{
    public function testAdminCanAccessCustomerFixedPrices(): void
    {
        $pageSize = config('downstairs.pageSize');
        $fixedPricesQuery = FixedPrice::withTrashed()
            ->whereHas('user', function (Builder $query) {
                $query->whereHas('roles', function (Builder $query) {
                    $query->where('name', 'Customer');
                });
            });

        $fixedPrices = $fixedPricesQuery->get();
        $count = $fixedPrices->filter(fn ($fixedPrice) => $fixedPrice->is_active)->count();
        $total = $count > $pageSize ? $pageSize : $count;

        $this->actingAs($this->admin)
            ->get('/customers/fixedprices')
            ->assertInertia(fn (Assert $page) => $page
                ->component('Customer/FixedPrice/index')
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

    public function testCustomerCanNotAccessCustomerFixedPrices(): void
    {
        $this->actingAs($this->user)
            ->get('/customers/fixedprices')
            ->assertInertia(fn (Assert $page) => $page
                ->component('Error/index')
                ->where('code', '404'));
    }

    public function testCanFilterCustomerFixedPrices(): void
    {
        $data = FixedPrice::whereHas('user', function (Builder $query) {
            $query->whereHas('roles', function (Builder $query) {
                $query->where('name', 'Customer');
            });
        })->first();
        $pageSize = config('downstairs.pageSize');

        $data->update([
            'start_date' => null,
            'end_date' => null,
        ]);

        $this->actingAs($this->admin)
            ->get("/customers/fixedprices?id.eq={$data->id}")
            ->assertInertia(fn (Assert $page) => $page
                ->component('Customer/FixedPrice/index')
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

    public function testAdminCanAccessCustomerFixedPricesJson(): void
    {
        $response = $this->actingAs($this->admin)
            ->get('/customers/fixedprices/json');
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

    public function testAdminCanAccessCustomerFixedPriceJson(): void
    {
        $data = FixedPrice::whereHas('user', function (Builder $query) {
            $query->whereHas('roles', function (Builder $query) {
                $query->where('name', 'Customer');
            });
        })->first();
        $response = $this->actingAs($this->admin)
            ->get("/customers/fixedprices/{$data->id}/json");
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

    public function testCanCreateCustomerFixedPrice(): void
    {
        $now = now();
        $customer = Customer::first();
        $userId = $customer->users()->first()->id;

        Invoice::findOrCreate(
            $userId,
            $customer->id,
            $now->month,
            $now->year,
            InvoiceTypeEnum::Cleaning()
        );

        $subscription = Subscription::first();
        $subscription->update([
            'fixed_price_id' => null,
        ]);

        $data = [
            'userId' => $customer->users()->first()->id,
            'customerId' => $customer->id,
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
            ->post('/customers/fixedprices', $data)
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

        $this->assertDatabaseMissing('invoices', [
            'user_id' => $data['userId'],
            'month' => $now->month,
            'year' => $now->year,
            'type' => InvoiceTypeEnum::CleaningAndLaundry(),
            'status' => InvoiceStatusEnum::Open(),
        ]);

        $this->assertDatabaseHas('invoices', [
            'user_id' => $data['userId'],
            'month' => $now->month,
            'year' => $now->year,
            'type' => InvoiceTypeEnum::Cleaning(),
            'status' => InvoiceStatusEnum::Open(),
        ]);
    }

    public function testCanCreateCustomerFixedPriceIncludeLaundry(): void
    {
        $now = now();
        $customer = Customer::first();
        $userId = $customer->users()->first()->id;

        Invoice::findOrCreate(
            $userId,
            $customer->id,
            $now->month,
            $now->year,
            InvoiceTypeEnum::Cleaning()
        );

        $subscription = Subscription::first();
        $subscription->update([
            'fixed_price_id' => null,
        ]);

        $data = [
            'userId' => $customer->users()->first()->id,
            'customerId' => $customer->id,
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
            ->post('/customers/fixedprices', $data)
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

        $this->assertDatabaseHas('invoices', [
            'user_id' => $data['userId'],
            'month' => $now->month,
            'year' => $now->year,
            'type' => InvoiceTypeEnum::CleaningAndLaundry(),
            'status' => InvoiceStatusEnum::Open(),
        ]);

        $this->assertDatabaseMissing('invoices', [
            'user_id' => $data['userId'],
            'month' => $now->month,
            'year' => $now->year,
            'type' => InvoiceTypeEnum::Cleaning(),
            'status' => InvoiceStatusEnum::Open(),
        ]);

        $this->assertDatabaseMissing('invoices', [
            'user_id' => $data['userId'],
            'month' => $now->month,
            'year' => $now->year,
            'type' => InvoiceTypeEnum::Laundry(),
            'status' => InvoiceStatusEnum::Open(),
        ]);
    }

    public function testCanNotCreateCustomerFixedPriceIncludeLaundryIfPerOrder(): void
    {
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
            ->post('/customers/fixedprices', $data)
            ->assertStatus(302)
            ->assertRedirect()
            ->assertSessionHas('error', __('laundry must be applied in monthly'));
    }

    public function testCanNotCreateCustomerFixedPriceIncludeLaundryIfExists(): void
    {
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
            ->post('/customers/fixedprices', $data)
            ->assertStatus(302)
            ->assertRedirect()
            ->assertSessionHas('error', __('laundry fixed price already exists'));
    }

    public function testCanNotCreateCustomerFixedPriceIfAlreadyHaveFixedPrice(): void
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
            ->post('/customers/fixedprices', $data)
            ->assertStatus(302)
            ->assertRedirect()
            ->assertSessionHas(
                'error',
                __('there are subscription that already have fixed price', ['count' => $count])
            );
    }

    public function testCanNotCustomerCreateFixedPriceIfRowNotUnique(): void
    {
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
            ->post('/customers/fixedprices', $data)
            ->assertStatus(302)
            ->assertRedirect()
            ->assertSessionHas('error', __('fixed price row type must be unique'));
    }

    public function testCanUpdateCustomerFixedPrice(): void
    {
        $now = now();
        $customer = Customer::first();
        $userId = $customer->users()->first()->id;

        Invoice::findOrCreate(
            $userId,
            $customer->id,
            $now->month,
            $now->year,
            InvoiceTypeEnum::Cleaning()
        );

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
            ->patch("/customers/fixedprices/{$fixedPrice->id}", $data)
            ->assertStatus(302)
            ->assertRedirect()
            ->assertSessionHas('success', __('fixed price updated successfully'));

        $this->assertDatabaseHas('fixed_prices', [
            'id' => $fixedPrice->id,
            'is_per_order' => $data['isPerOrder'],
        ]);

        $this->assertDatabaseHas('meta', [
            'metable_type' => FixedPrice::class,
            'metable_id' => $fixedPrice->id,
            'key' => 'include_laundry',
            'value' => '0',
            'type' => 'boolean',
        ]);

        $this->assertDatabaseMissing('invoices', [
            'user_id' => $fixedPrice->user_id,
            'type' => InvoiceTypeEnum::CleaningAndLaundry(),
            'month' => $now->month,
            'year' => $now->year,
            'status' => InvoiceStatusEnum::Open(),
        ]);

        $this->assertDatabaseHas('invoices', [
            'user_id' => $fixedPrice->user_id,
            'type' => InvoiceTypeEnum::Cleaning(),
            'month' => $now->month,
            'year' => $now->year,
            'status' => InvoiceStatusEnum::Open(),
        ]);
    }

    public function testCanUpdateCustomerFixedPriceIncludeLaundry(): void
    {
        $now = now();
        $customer = Customer::first();
        $userId = $customer->users()->first()->id;

        Invoice::findOrCreate(
            $userId,
            $customer->id,
            $now->month,
            $now->year,
            InvoiceTypeEnum::Cleaning()
        );

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

        $this->actingAs($this->admin)
            ->patch("/customers/fixedprices/{$fixedPrice->id}", $data)
            ->assertStatus(302)
            ->assertRedirect()
            ->assertSessionHas('success', __('fixed price updated successfully'));

        $this->assertDatabaseHas('fixed_prices', [
            'id' => $fixedPrice->id,
            'is_per_order' => $data['isPerOrder'],
        ]);

        $this->assertDatabaseHas('meta', [
            'metable_type' => FixedPrice::class,
            'metable_id' => $fixedPrice->id,
            'key' => 'include_laundry',
            'value' => '1',
            'type' => 'boolean',
        ]);

        $this->assertDatabaseHas('invoices', [
            'user_id' => $fixedPrice->user_id,
            'type' => InvoiceTypeEnum::CleaningAndLaundry(),
            'month' => $now->month,
            'year' => $now->year,
            'status' => InvoiceStatusEnum::Open(),
        ]);

        $this->assertDatabaseMissing('invoices', [
            'user_id' => $fixedPrice->user_id,
            'type' => InvoiceTypeEnum::Cleaning(),
            'month' => $now->month,
            'year' => $now->year,
            'status' => InvoiceStatusEnum::Open(),
        ]);

        $this->assertDatabaseMissing('invoices', [
            'user_id' => $fixedPrice->user_id,
            'type' => InvoiceTypeEnum::Laundry(),
            'month' => $now->month,
            'year' => $now->year,
            'status' => InvoiceStatusEnum::Open(),
        ]);
    }

    public function testCanNotUpdateCustomerFixedPriceIncludeLaundryIfPerOrder(): void
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
            ->patch("/customers/fixedprices/{$fixedPrice->id}", $data)
            ->assertStatus(302)
            ->assertRedirect()
            ->assertSessionHas(
                'error',
                __('laundry must be applied in monthly')
            );
    }

    public function testCanNotUpdateCustomerFixedPriceIncludeLaundryIfExists(): void
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
            ->patch("/customers/fixedprices/{$fixedPrice->id}", $data)
            ->assertStatus(302)
            ->assertRedirect()
            ->assertSessionHas(
                'error',
                __('laundry fixed price already exists')
            );
    }

    public function testCanNotUpdateCustomerFixedPriceIfAlreadyHaveFixedPrice(): void
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
            ->patch("/customers/fixedprices/{$fixedPrice->id}", $data)
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

    public function testCanDeleteCustomerFixedPrice(): void
    {
        $now = now();
        $customer = Customer::first();
        $userId = $customer->users()->first()->id;

        Invoice::findOrCreate(
            $userId,
            $customer->id,
            $now->month,
            $now->year,
            InvoiceTypeEnum::Cleaning()
        );

        $fixedPrice = FixedPrice::find(1);

        $this->actingAs($this->admin)
            ->delete('/customers/fixedprices/1')
            ->assertStatus(302)
            ->assertRedirect()
            ->assertSessionHas('success', __('fixed price deleted successfully'));

        $this->assertSoftDeleted('fixed_prices', [
            'id' => 1,
        ]);

        $this->assertDatabaseMissing('invoices', [
            'user_id' => $fixedPrice->user_id,
            'type' => InvoiceTypeEnum::CleaningAndLaundry(),
            'month' => $now->month,
            'year' => $now->year,
            'status' => InvoiceStatusEnum::Open(),
        ]);

        $this->assertDatabaseHas('invoices', [
            'user_id' => $fixedPrice->user_id,
            'type' => InvoiceTypeEnum::Cleaning(),
            'month' => $now->month,
            'year' => $now->year,
            'status' => InvoiceStatusEnum::Open(),
        ]);
    }

    public function testCanRestoreCustomerFixedPrice(): void
    {
        $now = now();
        $customer = Customer::first();
        $userId = $customer->users()->first()->id;

        Invoice::findOrCreate(
            $userId,
            $customer->id,
            $now->month,
            $now->year,
            InvoiceTypeEnum::Cleaning()
        );

        $fixedPrice = FixedPrice::first();
        $fixedPrice->delete();

        $this->actingAs($this->admin)
            ->post("/customers/fixedprices/{$fixedPrice->id}/restore")
            ->assertStatus(302)
            ->assertRedirect()
            ->assertSessionHas('success', __('fixed price restored successfully'));

        $this->assertDatabaseHas('fixed_prices', [
            'id' => $fixedPrice->id,
            'deleted_at' => null,
        ]);

        $this->assertDatabaseMissing('invoices', [
            'user_id' => $fixedPrice->user_id,
            'type' => InvoiceTypeEnum::CleaningAndLaundry(),
            'month' => $now->month,
            'year' => $now->year,
            'status' => InvoiceStatusEnum::Open(),
        ]);

        $this->assertDatabaseHas('invoices', [
            'user_id' => $fixedPrice->user_id,
            'type' => InvoiceTypeEnum::Cleaning(),
            'month' => $now->month,
            'year' => $now->year,
            'status' => InvoiceStatusEnum::Open(),
        ]);
    }

    public function testCanRestoreFixedPriceIncludeLaundry(): void
    {

        $now = now();
        $customer = Customer::first();
        $userId = $customer->users()->first()->id;

        Invoice::findOrCreate(
            $userId,
            $customer->id,
            $now->month,
            $now->year,
            InvoiceTypeEnum::Cleaning()
        );

        $fixedPrice = FixedPrice::create([
            'user_id' => $customer->users()->first()->id,
            'is_per_order' => false,
        ]);

        $fixedPrice->saveMeta([
            'include_laundry' => true,
        ]);
        $fixedPrice->delete();

        $this->actingAs($this->admin)
            ->post("/customers/fixedprices/{$fixedPrice->id}/restore")
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

        $this->assertDatabaseHas('invoices', [
            'customer_id' => $customer->id,
            'type' => InvoiceTypeEnum::CleaningAndLaundry(),
            'month' => $now->month,
            'year' => $now->year,
            'status' => InvoiceStatusEnum::Open(),
        ]);

        $this->assertDatabaseMissing('invoices', [
            'customer_id' => $customer->id,
            'type' => InvoiceTypeEnum::Cleaning(),
            'month' => $now->month,
            'year' => $now->year,
            'status' => InvoiceStatusEnum::Open(),
        ]);

        $this->assertDatabaseMissing('invoices', [
            'customer_id' => $customer->id,
            'type' => InvoiceTypeEnum::Laundry(),
            'month' => $now->month,
            'year' => $now->year,
            'status' => InvoiceStatusEnum::Open(),
        ]);
    }

    public function testCanNotRestoreFixedPriceIncludeLaundryIfExists(): void
    {
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
            ->post("/customers/fixedprices/{$fixedPrice->id}/restore")
            ->assertStatus(302)
            ->assertRedirect()
            ->assertSessionHas('error', __('laundry fixed price already exists'));
    }
}
