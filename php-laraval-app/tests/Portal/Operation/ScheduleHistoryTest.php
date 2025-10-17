<?php

namespace Tests\Portal\Operation;

use App\Enums\Discount\CustomerDiscountTypeEnum;
use App\Enums\FixedPrice\FixedPriceRowTypeEnum;
use App\Enums\Order\OrderPaidByEnum;
use App\Enums\Order\OrderStatusEnum;
use App\Enums\Product\ProductUnitEnum;
use App\Enums\ScheduleCleaning\ScheduleCleaningStatusEnum;
use App\Enums\VatNumbersEnum;
use App\Models\CustomerDiscount;
use App\Models\Product;
use App\Models\ScheduleCleaning;
use App\Models\Subscription;
use App\Models\User;
use Cache;
use Carbon\Carbon;
use Illuminate\Http\Response;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class ScheduleHistoryTest extends TestCase
{
    public function testCanCreateScheduleHistory(): void
    {

        Product::find(config('downstairs.products.material.id'))
            ->update(['fortnox_article_id' => 40]);
        Cache::forget('material');
        $customer = $this->user->customers->first();
        $this->user->subscriptions()->forceDelete();
        $this->user->fixedPrices()->forceDelete();

        $startAt = now()->addDay()->format('Y-m-d');
        $productIds = [1, 2];
        // find worker role in user
        $worker2 = User::role('Worker')->first();
        $workers = [
            [
                'userId' => $this->worker->id,
                'startAt' => Carbon::parse($startAt.' 04:00:00')->format('Y-m-d H:i:s'),
                'endAt' => Carbon::parse($startAt.' 05:00:00')->format('Y-m-d H:i:s'),
            ],
            [
                'userId' => $worker2->id,
                'startAt' => Carbon::parse($startAt.' 05:00:00')->format('Y-m-d H:i:s'),
                'endAt' => Carbon::parse($startAt.' 06:00:00')->format('Y-m-d H:i:s'),
            ],
        ];
        $data = [
            'userId' => $this->user->id,
            'propertyId' => $this->user->properties->first()->id,
            'customerId' => $customer->id,
            'teamId' => $this->team->id,
            'serviceId' => 1,
            'productIds' => $productIds,
            'description' => 'test',
            'quarters' => 8,
            // time and frequency
            'startAt' => $startAt,
            'startTimeAt' => '04:00:00',
            // fixed price
            'totalPrice' => 9999,
            // workers
            'workers' => $workers,
        ];

        $this->actingAs($this->admin)
            ->postJson('/schedules/history', $data)
            ->assertStatus(Response::HTTP_CREATED)
            ->assertJson(fn (AssertableJson $json) => $json
                ->whereAllType([
                    'message' => 'string',
                    'data' => 'array',
                ]));

        $this->assertDatabaseHas('subscriptions', [
            'user_id' => $data['userId'],
            'customer_id' => $data['customerId'],
            'property_id' => $data['propertyId'],
            'team_id' => $data['teamId'],
            'service_id' => $data['serviceId'],
            'description' => $data['description'],
            'quarters' => $data['quarters'],
            'frequency' => 0,
            'start_at' => $data['startAt'],
            'end_at' => $data['startAt'],
            'start_time_at' => $data['startTimeAt'],
            'refill_sequence' => 1,
        ]);

        $subscription = Subscription::where('user_id', $this->user->id)->first();
        foreach ($productIds as $productId) {
            $this->assertDatabaseHas('subscription_product', [
                'subscription_id' => $subscription->id,
                'product_id' => $productId,
                'quantity' => 1,
            ]);
        }

        foreach ($workers as $worker) {
            $this->assertDatabaseHas('subscription_staff_details', [
                'subscription_id' => $subscription->id,
                'user_id' => $worker['userId'],
                'quarters' => $data['quarters'],
                'is_active' => true,
            ]);
        }

        $this->assertEquals(1, $subscription->scheduleCleanings->count());

        $scheduleStartAt = Carbon::parse($data['startAt'].' '.$data['startTimeAt']);
        $hours = $data['quarters'] / 4 / count($workers);
        $this->assertDatabaseHas('schedule_cleanings', [
            'subscription_id' => $subscription->id,
            'team_id' => $data['teamId'],
            'customer_id' => $data['customerId'],
            'property_id' => $data['propertyId'],
            'status' => ScheduleCleaningStatusEnum::Done(),
            'start_at' => $scheduleStartAt->format('Y-m-d H:i:s'),
            'end_at' => $scheduleStartAt->copy()->addHours($hours)->format('Y-m-d H:i:s'),
            'quarters' => $data['quarters'],
        ]);

        $scheduleCleaning = $subscription->scheduleCleanings->first();
        foreach ($workers as $worker) {
            $this->assertDatabaseHas('schedule_employees', [
                'user_id' => $worker['userId'],
                'scheduleable_type' => ScheduleCleaning::class,
                'scheduleable_id' => $scheduleCleaning->id,
                'start_at' => $worker['startAt'],
                'end_at' => $worker['endAt'],
                'status' => ScheduleCleaningStatusEnum::Done(),
            ]);
        }

        $this->assertDatabaseHas('fixed_prices', [
            'user_id' => $data['userId'],
            'is_per_order' => true,
        ]);

        $vat = VatNumbersEnum::TwentyFive();
        $price = $data['totalPrice'] / (1 + $vat / 100);
        $this->assertDatabaseHas('fixed_price_rows', [
            'type' => FixedPriceRowTypeEnum::Service(),
            'quantity' => 1,
            'price' => $price,
            'vat_group' => $vat,
            'has_rut' => true,
        ]);

        $this->assertDatabaseHas('orders', [
            'user_id' => $data['userId'],
            'customer_id' => $data['customerId'],
            'service_id' => $data['serviceId'],
            'subscription_id' => $subscription->id,
            'orderable_type' => ScheduleCleaning::class,
            'orderable_id' => $scheduleCleaning->id,
            'status' => OrderStatusEnum::Draft(),
            'paid_by' => OrderPaidByEnum::Invoice(),
        ]);

        $service = $subscription->service;
        $discount = CustomerDiscount::getCurrentDiscountByUser(
            $subscription->user_id,
            CustomerDiscountTypeEnum::Cleaning(),
        );

        $this->assertDatabaseHas('order_rows', [
            'description' => $service->name,
            'fortnox_article_id' => $service->fortnox_article_id,
            'quantity' => ($data['quarters'] ?? $subscription->quarters) / 4,
            'unit' => ProductUnitEnum::Hours(),
            'price' => $service->price * 4,
            'discount_percentage' => $discount ? $discount->value : 0,
            'vat' => $service->vat_group,
            'has_rut' => $service->has_rut,
        ]);

        $material = get_material();
        foreach ($scheduleCleaning->products as $product) {
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
    }
}
