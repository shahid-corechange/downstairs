<?php

namespace Tests\Portal\Employee;

use App\DTOs\Deviation\ScheduleDeviationResponseDTO;
use App\Enums\Discount\CustomerDiscountTypeEnum;
use App\Enums\Order\OrderStatusEnum;
use App\Enums\Product\ProductUnitEnum;
use App\Enums\ScheduleEmployee\ScheduleEmployeeStatusEnum;
use App\Jobs\SentWorkingHoursJob;
use App\Models\CustomerDiscount;
use App\Models\Product;
use App\Models\ScheduleCleaning;
use App\Models\ScheduleCleaningDeviation;
use App\Models\ScheduleDeviation;
use Bus;
use Cache;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class DeviationTest extends TestCase
{
    public function testAdminCanAccessDeviations(): void
    {
        $pageSize = config('downstairs.pageSize');
        $count = ScheduleCleaningDeviation::count();
        $total = $count > $pageSize ? $pageSize : $count;

        $this->actingAs($this->admin)
            ->get('/deviations')
            ->assertInertia(fn (Assert $page) => $page
                ->component('Deviation/Overview/index')
                ->has('deviations', $total)
                ->has('deviations.0', fn (Assert $page) => $page
                    ->has('id')
                    ->has('types')
                    ->has('isHandled')
                    ->has('scheduleCleaning', fn (Assert $page) => $page
                        ->has('startAt')
                        ->has('endAt')
                        ->has('subscription', fn (Assert $page) => $page
                            ->has('user', fn (Assert $page) => $page
                                ->has('fullname')))
                        ->has('team', fn (Assert $page) => $page
                            ->has('id')
                            ->has('name'))))
                ->has('pagination', fn (Assert $page) => $page
                    ->where('total', $count)
                    ->where('size', $pageSize)
                    ->where('currentPage', 1)
                    ->etc()));
    }

    public function testCustomerCanNotAccessDeviations(): void
    {
        $this->actingAs($this->user)
            ->get('/deviations')
            ->assertInertia(fn (Assert $page) => $page
                ->component('Error/index')
                ->where('code', '404'));
    }

    public function testCanFilterDeviations(): void
    {
        $data = ScheduleCleaningDeviation::first();
        $pageSize = config('downstairs.pageSize');

        $this->actingAs($this->admin)
            ->get("/deviations?id.eq={$data->id}")
            ->assertInertia(fn (Assert $page) => $page
                ->component('Deviation/Overview/index')
                ->has('deviations', 1)
                ->has('deviations.0', fn (Assert $page) => $page
                    ->where('id', $data->id)
                    ->where('types', $data->types)
                    ->where('isHandled', $data->is_handled)
                    ->has('scheduleCleaning', fn (Assert $page) => $page
                        ->has('startAt')
                        ->has('endAt')
                        ->has('subscription', fn (Assert $page) => $page
                            ->has('user', fn (Assert $page) => $page
                                ->has('fullname')))
                        ->has('team', fn (Assert $page) => $page
                            ->has('id')
                            ->has('name'))))
                ->has('pagination', fn (Assert $page) => $page
                    ->where('total', 1)
                    ->where('size', $pageSize)
                    ->where('currentPage', 1)
                    ->etc()));
    }

    public function testAdminCanAccessDeviationsJson(): void
    {
        $response = $this->actingAs($this->admin)
            ->get('/deviations/json');
        $keys = array_keys(
            ScheduleDeviationResponseDTO::from(ScheduleDeviation::first())->toArray()
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

    public function testAdminCanAccessDeviationJson(): void
    {
        $response = $this->actingAs($this->admin)
            ->get('/deviations/1/json');
        $keys = array_keys(
            ScheduleDeviationResponseDTO::from(ScheduleDeviation::first())->toArray()
        );

        $response->assertStatus(200);

        $response->assertJsonStructure([
            'data' => $keys,
            'meta' => [
                'etag',
            ],
        ]);
    }

    public function testCanHandleDeviation(): void
    {
        Product::find(config('downstairs.products.material.id'))
            ->update(['fortnox_article_id' => 40]);
        Cache::forget('material');
        $deviation = ScheduleCleaningDeviation::unhandled()->first();
        /** @var ScheduleCleaning $schedule */
        $schedule = $deviation->scheduleCleaning;
        $products = $schedule->products->map(fn ($product) => [
            'id' => $product->id,
            'isCharge' => true,
        ])->toArray();
        $discount = CustomerDiscount::getCurrentDiscountByUser(
            $schedule->subscription->user_id,
            CustomerDiscountTypeEnum::Cleaning(),
        );

        if (! $discount) {
            $discount = CustomerDiscount::create([
                'user_id' => $schedule->subscription->user_id,
                'type' => CustomerDiscountTypeEnum::Cleaning(),
                'value' => 10,
            ]);
        }

        $data = [
            'actualQuarters' => 1,
            'products' => $products,
        ];
        $this->actingAs($this->admin)
            ->post("/deviations/$deviation->id/handle", $data)
            ->assertStatus(302)
            ->assertRedirect()
            ->assertSessionHas('success', __('deviation handled successfully'));

        $this->assertDatabaseHas('schedule_cleaning_deviations', [
            'id' => $deviation->id,
            'is_handled' => true,
        ]);

        $schedule->refresh();
        // $this->assertDatabaseHas('schedule_cleanings', [
        //     'id' => $schedule->id,
        //     'quarters' => $data['actualQuarters'],
        //     'status' => $schedule->actual_start_at && $schedule->actual_end_at
        //             ? ScheduleCleaningStatusEnum::Done()
        //             : ScheduleCleaningStatusEnum::Cancel(),
        // ]);

        $this->assertDatabaseHas('orders', [
            'user_id' => $schedule->subscription->user_id,
            'customer_id' => $schedule->subscription->customer_id,
            'service_id' => $schedule->subscription->service_id,
            'subscription_id' => $schedule->subscription_id,
            'status' => OrderStatusEnum::Draft(),
        ]);

        $service = $schedule->subscription->service;
        $serviceQuantity = $data['actualQuarters'] ?? $schedule->subscription->quarters;

        $this->assertDatabaseHas('order_rows', [
            'description' => $service->name,
            'fortnox_article_id' => $service->fortnox_article_id,
            'quantity' => $serviceQuantity / 4,
            'unit' => ProductUnitEnum::Hours(),
            'price' => $service->price * 4,
            'discount_percentage' => $discount ? $discount->value : 0,
            'vat' => $service->vat_group,
            'has_rut' => $service->has_rut,
        ]);

        $material = get_material();
        foreach ($schedule->products as $product) {
            $isMaterial = $product->product->fortnox_article_id === $material->fortnox_article_id;
            $this->assertDatabaseHas('order_rows', [
                'fortnox_article_id' => $product->product->fortnox_article_id,
                'quantity' => $isMaterial ? $product->quantity / 4 : $product->quantity,
                'unit' => $product->product->unit,
                'price' => $isMaterial ? $product->price * 4 : $product->price,
                'discount_percentage' => max($product->discount_percentage, $discount ? $discount->value : 0),
                'vat' => $product->product->vat_group,
                'has_rut' => $product->product->has_rut,
            ]);
        }

        $isDoneExists = $schedule->scheduleEmployees()
            ->where('status', ScheduleEmployeeStatusEnum::Done())
            ->exists();

        if ($isDoneExists) {
            Bus::assertDispatchedAfterResponse(SentWorkingHoursJob::class);
        } else {
            Bus::assertNotDispatched(SentWorkingHoursJob::class);
        }
    }

    public function testCanNotHandleDeviationIfAttendaceIncomplated(): void
    {
        $deviation = ScheduleCleaningDeviation::unhandled()->first();
        $deviation->scheduleCleaning
            ->scheduleEmployees()
            ->first()
            ->update([
                'start_at' => now(),
                'end_at' => null,
            ]);

        $data = [
            'actualQuarters' => 1,
            'products' => [
                [
                    'id' => 1,
                    'isCharge' => true,
                ],
            ],
        ];

        $this->actingAs($this->admin)
            ->post("/deviations/$deviation->id/handle", $data)
            ->assertStatus(302)
            ->assertRedirect()
            ->assertSessionHas('error', __('deviation has incomplete workers attendance'));
    }

    public function testCanNotHandleDeviationIfAlreadyHandled(): void
    {
        $deviation = ScheduleCleaningDeviation::first();
        $deviation->update(['is_handled' => true]);

        $data = [
            'actualQuarters' => 1,
            'products' => [
                [
                    'id' => 1,
                    'isCharge' => true,
                ],
            ],
        ];

        $this->actingAs($this->admin)
            ->post("/deviations/$deviation->id/handle", $data)
            ->assertStatus(302)
            ->assertRedirect()
            ->assertSessionHas('error', __('deviation already handled'));
    }
}
