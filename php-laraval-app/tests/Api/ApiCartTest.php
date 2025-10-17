<?php

namespace Tests\Api;

use App\Enums\Auth\TokenAbilityEnum;
use App\Enums\Credit\CreditTransactionTypeEnum;
use App\Enums\GlobalSetting\GlobalSettingEnum;
use App\Enums\ScheduleCleaning\CleaningProductPaymentMethodEnum;
use App\Enums\ScheduleCleaning\ScheduleCleaningStatusEnum;
use App\Enums\ScheduleEmployee\ScheduleEmployeeStatusEnum;
use App\Jobs\SendNotificationJob;
use App\Models\Credit;
use App\Models\CreditCreditTransaction;
use App\Models\CreditTransaction;
use App\Models\Product;
use App\Models\ScheduleCleaning;
use App\Models\Subscription;
use Bus;
use Illuminate\Http\Response;
use Illuminate\Testing\Fluent\AssertableJson;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ApiCartTest extends TestCase
{
    public function testSuccessCheckoutNotUsingCreditWithApi(): void
    {
        $maxHourToAddProduct = get_setting(GlobalSettingEnum::MaxProductAddTime(), 12);
        Sanctum::actingAs($this->user, [TokenAbilityEnum::APIAccess()]);
        $subscription = Subscription::where('user_id', $this->user->id)->first();
        $scheduleCleaning1 = ScheduleCleaning::factory()
            ->forSubscription($subscription)
            ->forStatus(ScheduleCleaningStatusEnum::Booked())
            ->create();
        $scheduleCleaning1->scheduleEmployees()->create([
            'user_id' => $this->worker->id,
            'status' => ScheduleEmployeeStatusEnum::Pending(),
        ]);
        $scheduleCleaning1->update([
            'start_time' => now()->addHours($maxHourToAddProduct),
            'end_time' => now()->addHours($maxHourToAddProduct + 1),
        ]);
        $scheduleCleaning2 = ScheduleCleaning::factory()
            ->forSubscription($subscription)
            ->forStatus(ScheduleCleaningStatusEnum::Booked())
            ->create();
        $scheduleCleaning2->scheduleEmployees()->create([
            'user_id' => $this->worker->id,
            'status' => ScheduleEmployeeStatusEnum::Pending(),
        ]);
        $scheduleCleaning2->update([
            'start_time' => now()->addHours($maxHourToAddProduct),
            'end_time' => now()->addHours($maxHourToAddProduct + 1),
        ]);

        $response = $this->postJson(
            '/api/v0/carts/checkout',
            [
                'products' => [
                    ['productId' => 1, 'scheduleId' => $scheduleCleaning1->id, 'quantity' => 1],
                    ['productId' => 2, 'scheduleId' => $scheduleCleaning2->id, 'quantity' => 1],
                ],
                'useCredit' => false,
            ]
        );

        $response->assertStatus(Response::HTTP_NO_CONTENT);

        $product1 = Product::find(1);
        $this->assertDatabaseHas('schedule_cleaning_products', [
            'schedule_cleaning_id' => $scheduleCleaning1->id,
            'product_id' => 1,
            'price' => $product1->price,
            'quantity' => 1,
            'discount_percentage' => 0,
            'payment_method' => CleaningProductPaymentMethodEnum::Invoice(),
        ]);

        $product2 = Product::find(2);
        $this->assertDatabaseHas('schedule_cleaning_products', [
            'schedule_cleaning_id' => $scheduleCleaning2->id,
            'product_id' => 2,
            'price' => $product2->price,
            'quantity' => 1,
            'discount_percentage' => 0,
            'payment_method' => CleaningProductPaymentMethodEnum::Invoice(),
        ]);

        Bus::assertDispatchedAfterResponse(SendNotificationJob::class);
    }

    public function testSuccessCheckoutUsingCreditWithApi(): void
    {
        $maxHourToAddProduct = get_setting(GlobalSettingEnum::MaxProductAddTime(), 12);
        Sanctum::actingAs($this->user, [TokenAbilityEnum::APIAccess()]);
        $credit = Credit::factory()->forUser($this->user->id)->create();
        $credit->update([
            'initial_amount' => 100,
            'remaining_amount' => 100,
            'valid_until' => now()->addDay(),
        ]);
        $transaction = CreditTransaction::create([
            'user_id' => $credit->user_id,
            'schedule_cleaning_id' => null,
            'type' => CreditTransactionTypeEnum::Granted(),
            'total_amount' => 100,
            'description' => 'Granted credit',
        ]);

        CreditCreditTransaction::create([
            'credit_id' => $credit->id,
            'credit_transaction_id' => $transaction->id,
            'amount' => 100,
        ]);
        $subscription = Subscription::where('user_id', $this->user->id)->first();
        $scheduleCleaning1 = ScheduleCleaning::factory()
            ->forSubscription($subscription)
            ->forStatus(ScheduleCleaningStatusEnum::Booked())
            ->create();
        $scheduleCleaning1->update([
            'start_time' => now()->addHours($maxHourToAddProduct),
            'end_time' => now()->addHours($maxHourToAddProduct + 1),
        ]);
        $scheduleCleaning1->scheduleEmployees()->create([
            'user_id' => $this->worker->id,
            'status' => ScheduleEmployeeStatusEnum::Pending(),
        ]);
        $scheduleCleaning2 = ScheduleCleaning::factory()
            ->forSubscription($subscription)
            ->forStatus(ScheduleCleaningStatusEnum::Booked())
            ->create();
        $scheduleCleaning2->update([
            'start_time' => now()->addHours($maxHourToAddProduct),
            'end_time' => now()->addHours($maxHourToAddProduct + 1),
        ]);
        $scheduleCleaning2->scheduleEmployees()->create([
            'user_id' => $this->worker->id,
            'status' => ScheduleEmployeeStatusEnum::Pending(),
        ]);

        $response = $this->postJson(
            '/api/v0/carts/checkout',
            [
                'products' => [
                    ['productId' => 1, 'scheduleId' => $scheduleCleaning1->id, 'quantity' => 1],
                    ['productId' => 2, 'scheduleId' => $scheduleCleaning2->id, 'quantity' => 1],
                ],
                'useCredit' => true,
            ]
        );

        $response->assertStatus(Response::HTTP_NO_CONTENT);

        $product1 = Product::find(1);
        $this->assertDatabaseHas('schedule_cleaning_products', [
            'schedule_cleaning_id' => $scheduleCleaning1->id,
            'product_id' => 1,
            'price' => $product1->price,
            'quantity' => 1,
            'discount_percentage' => 100,
            'payment_method' => CleaningProductPaymentMethodEnum::Credit(),
        ]);

        $product2 = Product::find(2);
        $this->assertDatabaseHas('schedule_cleaning_products', [
            'schedule_cleaning_id' => $scheduleCleaning2->id,
            'product_id' => 2,
            'price' => $product2->price,
            'quantity' => 1,
            'discount_percentage' => 100,
            'payment_method' => CleaningProductPaymentMethodEnum::Credit(),
        ]);

        $this->assertDatabaseHas('credits', [
            'id' => $credit->id,
            'user_id' => $this->user->id,
            'schedule_cleaning_id' => null,
            'issuer_id' => null,
            'initial_amount' => 100,
            'remaining_amount' => 100 - ($product1->credit_price + $product2->credit_price),
            'type' => CreditTransactionTypeEnum::Granted(),
        ]);

        $this->assertDatabaseHas('credit_transactions', [
            'user_id' => $this->user->id,
            'schedule_cleaning_id' => $scheduleCleaning1->id,
            'issuer_id' => null,
            'type' => CreditTransactionTypeEnum::Payment(),
            'total_amount' => $product1->credit_price,
            'description' => $product1->name,
        ]);

        $this->assertDatabaseHas('credit_transactions', [
            'user_id' => $this->user->id,
            'schedule_cleaning_id' => $scheduleCleaning2->id,
            'issuer_id' => null,
            'type' => CreditTransactionTypeEnum::Payment(),
            'total_amount' => $product2->credit_price,
            'description' => $product2->name,
        ]);

        Bus::assertDispatchedAfterResponse(SendNotificationJob::class);
    }

    public function testFailInsufficientCreditCheckoutWithApi(): void
    {
        Sanctum::actingAs($this->user, [TokenAbilityEnum::APIAccess()]);
        $subscription = Subscription::where('user_id', $this->user->id)->first();
        $scheduleCleaning1 = ScheduleCleaning::factory()
            ->forSubscription($subscription)
            ->forStatus(ScheduleCleaningStatusEnum::Booked())
            ->create();
        $scheduleCleaning2 = ScheduleCleaning::factory()
            ->forSubscription($subscription)
            ->forStatus(ScheduleCleaningStatusEnum::Booked())
            ->create();

        $response = $this->postJson(
            '/api/v0/carts/checkout',
            [
                'products' => [
                    [
                        'productId' => Product::where('credit_price', '>', 0)->first()->id,
                        'scheduleId' => $scheduleCleaning1->id,
                        'quantity' => 1,
                    ],
                    [
                        'productId' => Product::where('credit_price', '>', 0)->first()->id,
                        'scheduleId' => $scheduleCleaning2->id,
                        'quantity' => 1,
                    ],
                ],
                'useCredit' => true,
            ]
        );

        $response->assertStatus(Response::HTTP_BAD_REQUEST)
            ->assertJson(fn (AssertableJson $json) => $json
                ->whereAllType([
                    'apiVersion' => 'string',
                    'error' => 'array',
                ]));
    }
}
