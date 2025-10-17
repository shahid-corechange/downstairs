<?php

namespace Tests\Api;

use App\DTOs\ScheduleCleaning\ScheduleCleaningResponseDTO;
use App\Enums\Auth\TokenAbilityEnum;
use App\Enums\Credit\CreditTransactionTypeEnum;
use App\Enums\Discount\CustomerDiscountTypeEnum;
use App\Enums\GlobalSetting\GlobalSettingEnum;
use App\Enums\Invoice\InvoiceStatusEnum;
use App\Enums\Invoice\InvoiceTypeEnum;
use App\Enums\Order\OrderStatusEnum;
use App\Enums\Product\ProductUnitEnum;
use App\Enums\ScheduleCleaning\CleaningProductPaymentMethodEnum;
use App\Enums\ScheduleCleaning\ScheduleCleaningChangeStatusEnum;
use App\Enums\ScheduleCleaning\ScheduleCleaningStatusEnum;
use App\Enums\ScheduleEmployee\ScheduleEmployeeStatusEnum;
use App\Enums\VatNumbersEnum;
use App\Jobs\SendNotificationJob;
use App\Models\CustomerDiscount;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\ScheduleCleaning;
use App\Models\Subscription;
use Bus;
use Carbon\Carbon;
use Illuminate\Http\Response;
use Illuminate\Testing\Fluent\AssertableJson;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ApiScheduleCleaningTest extends TestCase
{
    public function testAuthenticatedUserCanGetSchedulesWithApi(): void
    {
        Sanctum::actingAs($this->user, [TokenAbilityEnum::APIAccess()]);
        $response = $this->getJson('/api/v0/schedule-cleanings');

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson(fn (AssertableJson $json) => $json
                ->whereAllType([
                    'apiVersion' => 'string',
                    'data' => 'array',
                    'pagination' => 'array',
                    'meta' => 'array',
                ]));
    }

    public function testAuthenticatedUserCanGetSchedulesWithApiWithQuery(): void
    {
        Sanctum::actingAs($this->user, [TokenAbilityEnum::APIAccess()]);
        $response = $this->getJson('/api/v0/schedule-cleanings?page=1&size=2&sort=teamId.asc');

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson(fn (AssertableJson $json) => $json
                ->whereAllType([
                    'apiVersion' => 'string',
                    'data' => 'array',
                    'pagination' => 'array',
                    'meta' => 'array',
                ]));
    }

    public function testAuthenticatedUserCanNotGetSchedulesWithApiWithQuery(): void
    {
        Sanctum::actingAs($this->user, [TokenAbilityEnum::APIAccess()]);
        $response = $this->getJson('/api/v0/schedule-cleanings?page=1&size=2&teamId.id.between=1');

        $response->assertStatus(Response::HTTP_BAD_REQUEST)
            ->assertJson(fn (AssertableJson $json) => $json
                ->whereAllType([
                    'apiVersion' => 'string',
                    'error' => 'array',
                ]));
    }

    public function testNotAuthenticatedUserCanNotGetSchedulesWithApi(): void
    {
        $response = $this->getJson('/api/v0/schedule-cleanings');

        $response->assertStatus(Response::HTTP_UNAUTHORIZED)
            ->assertJson(fn (AssertableJson $json) => $json
                ->whereAllType([
                    'apiVersion' => 'string',
                    'error' => 'array',
                ]));
    }

    public function testAuthenticatedUserCanCancelScheduleAndNotGetCreditWithApi(): void
    {
        Sanctum::actingAs($this->user, [TokenAbilityEnum::APIAccess()]);

        $refundTime = get_setting(GlobalSettingEnum::CreditRefundTimeWindow(), 72);

        $subscription = Subscription::where('user_id', $this->user->id)->first();
        $scheduleCleaning = ScheduleCleaning::factory()
            ->forSubscription($subscription)
            ->forStatus(ScheduleCleaningStatusEnum::Pending())
            ->create();
        $scheduleCleaning->update([
            'start_at' => now()->addHours($refundTime + 1),
            'end_at' => now()->addHours($refundTime + 2),
        ]);
        $scheduleCleaning->scheduleEmployees()->create([
            'user_id' => $this->worker->id,
            'status' => ScheduleEmployeeStatusEnum::Pending(),
        ]);
        $scheduleCleaning->changeRequest()->create([
            'start_at_changed' => $scheduleCleaning->start_at,
            'end_at_changed' => $scheduleCleaning->end_at,
            'status' => ScheduleCleaningChangeStatusEnum::Pending(),
        ]);
        $scheduleCleaning->products()->create([
            'product_id' => 1,
            'quantity' => 1,
            'price' => 100,
            'discount_percentage' => 100,
            'payment_method' => CleaningProductPaymentMethodEnum::Credit(),
        ]);
        $response = $this->deleteJson("/api/v0/schedule-cleanings/{$scheduleCleaning->id}/cancel");

        $keys = array_keys(
            ScheduleCleaningResponseDTO::transformData($scheduleCleaning, [
                'subscription.service',
                'team',
                'customer.address.city.country',
                'property.address.city.country',
                'property.type',
                'products',
                'changeRequest',
            ])
        );
        $response->assertStatus(Response::HTTP_OK)
            ->assertJson(fn (AssertableJson $json) => $json
                ->whereAllType([
                    'apiVersion' => 'string',
                    'data' => 'array',
                ])
                ->has('data', fn (AssertableJson $json) => $json
                    ->hasAll($keys)));

        app()->setLocale('sv_SE');

        $this->assertDatabaseHas('schedule_cleanings', [
            'id' => $scheduleCleaning->id,
            'status' => ScheduleCleaningStatusEnum::Cancel(),
        ]);

        foreach ($scheduleCleaning->scheduleEmployees as $scheduleEmployee) {
            $this->assertDatabaseHas('schedule_employees', [
                'id' => $scheduleEmployee->id,
                'status' => ScheduleEmployeeStatusEnum::Cancel(),
            ]);
        }

        $type = Invoice::getUserType(
            $subscription->user_id,
            $subscription->customer->membership_type,
            InvoiceTypeEnum::Cleaning()
        );
        $invoice = Invoice::where('customer_id', $subscription->customer_id)
            ->where('type', $type)
            ->where('month', $scheduleCleaning->start_at->month)
            ->where('year', $scheduleCleaning->start_at->year)
            ->where('status', InvoiceStatusEnum::Open())
            ->first();

        $this->assertEquals(null, $invoice);

        $this->assertSoftDeleted('schedule_cleaning_change_requests', [
            'schedule_cleaning_id' => $scheduleCleaning->id,
        ]);

        $product = Product::find(1);

        $this->assertDatabaseHas('credit_transactions', [
            'user_id' => $scheduleCleaning->subscription->user_id,
            'schedule_cleaning_id' => $scheduleCleaning->id,
            'type' => CreditTransactionTypeEnum::Refund(),
            'total_amount' => $product->credit_price,
        ]);

        $this->assertDatabaseMissing('orders', [
            'orderable_type' => ScheduleCleaning::class,
            'orderable_id' => $scheduleCleaning->id,
        ]);

        Bus::assertDispatchedAfterResponse(SendNotificationJob::class);
    }

    public function testAuthenticatedUserCanCancelScheduleAndGetCreditWithApi(): void
    {
        Sanctum::actingAs($this->user, [TokenAbilityEnum::APIAccess()]);

        $refundTime = get_setting(GlobalSettingEnum::CreditRefundTimeWindow(), 72);
        $discount = CustomerDiscount::getCurrentDiscountByUser(
            $this->user->id,
            CustomerDiscountTypeEnum::Cleaning(),
        );

        $subscription = Subscription::where('user_id', $this->user->id)->first();
        $scheduleCleaning = ScheduleCleaning::factory()
            ->forSubscription($subscription)
            ->forStatus(ScheduleCleaningStatusEnum::Pending())
            ->create();
        $scheduleCleaning->update([
            'start_at' => now()->addHours($refundTime),
            'end_at' => now()->addHours($refundTime + 1),
        ]);
        $scheduleCleaning->scheduleEmployees()->create([
            'user_id' => $this->worker->id,
            'status' => ScheduleEmployeeStatusEnum::Pending(),
        ]);
        $scheduleCleaning->products()->create([
            'product_id' => 1,
            'quantity' => 1,
            'price' => 100,
            'discount_percentage' => 100,
            'payment_method' => CleaningProductPaymentMethodEnum::Credit(),
        ]);
        $response = $this->deleteJson("/api/v0/schedule-cleanings/{$scheduleCleaning->id}/cancel");

        $keys = array_keys(
            ScheduleCleaningResponseDTO::transformData($scheduleCleaning, [
                'subscription.service',
                'team',
                'customer.address.city.country',
                'property.address.city.country',
                'property.type',
                'products',
                'changeRequest',
            ])
        );
        $response->assertStatus(Response::HTTP_OK)
            ->assertJson(fn (AssertableJson $json) => $json
                ->whereAllType([
                    'apiVersion' => 'string',
                    'data' => 'array',
                ])
                ->has('data', fn (AssertableJson $json) => $json
                    ->hasAll($keys)));

        app()->setLocale('sv_SE');

        $this->assertDatabaseHas('schedule_cleanings', [
            'id' => $scheduleCleaning->id,
            'status' => ScheduleCleaningStatusEnum::Cancel(),
        ]);

        foreach ($scheduleCleaning->scheduleEmployees as $scheduleEmployee) {
            $this->assertDatabaseHas('schedule_employees', [
                'id' => $scheduleEmployee->id,
                'status' => ScheduleEmployeeStatusEnum::Cancel(),
            ]);
        }

        $type = Invoice::getUserType(
            $subscription->user_id,
            $subscription->customer->membership_type,
            InvoiceTypeEnum::Cleaning()
        );
        $invoice = Invoice::where('customer_id', $subscription->customer_id)
            ->where('type', $type)
            ->where('month', $scheduleCleaning->start_at->month)
            ->where('year', $scheduleCleaning->start_at->year)
            ->where('status', InvoiceStatusEnum::Open())
            ->first();

        $this->assertInstanceOf(Invoice::class, $invoice);

        $this->assertDatabaseHas('orders', [
            'user_id' => $this->user->id,
            'customer_id' => $subscription->customer_id,
            'service_id' => $subscription->service_id,
            'invoice_id' => $invoice->id,
            'subscription_id' => $subscription->id,
            'status' => OrderStatusEnum::Draft(),
        ]);

        $this->assertDatabaseHas('order_rows', [
            'description' => $subscription->service->name.get_credit_refund_description(),
            'fortnox_article_id' => $subscription->service->fortnox_article_id,
            'quantity' => $scheduleCleaning->quarters,
            'unit' => ProductUnitEnum::Piece(),
            'price' => $subscription->service->price,
            'discount_percentage' => $discount?->value ?? 0,
            'vat' => VatNumbersEnum::TwentyFive(),
            'has_rut' => $subscription->service->has_rut ? 1 : 0,
        ]);

        $minutePerCredit = get_setting(GlobalSettingEnum::CreditMinutePerCredit(), 15);
        $totalCreditAmount = $scheduleCleaning->quarters * 15 / $minutePerCredit;
        $this->assertDatabaseHas('credit_transactions', [
            'user_id' => $this->user->id,
            'schedule_cleaning_id' => $scheduleCleaning->id,
            'issuer_id' => null,
            'type' => CreditTransactionTypeEnum::Refund(),
            'total_amount' => $totalCreditAmount,
        ]);

        $this->assertDatabaseHas('credits', [
            'user_id' => $this->user->id,
            'schedule_cleaning_id' => $scheduleCleaning->id,
            'issuer_id' => null,
            'initial_amount' => $totalCreditAmount,
            'remaining_amount' => $totalCreditAmount,
            'type' => CreditTransactionTypeEnum::Refund(),
        ]);

        $product = Product::find(1);
        $this->assertDatabaseHas('credit_transactions', [
            'user_id' => $scheduleCleaning->subscription->user_id,
            'schedule_cleaning_id' => $scheduleCleaning->id,
            'type' => CreditTransactionTypeEnum::Refund(),
            'total_amount' => $product->credit_price,
        ]);

        $order = $scheduleCleaning->order;
        $this->assertDatabaseHas('order_rows', [
            'order_id' => $order->id,
            'description' => $scheduleCleaning->subscription->service->name.get_credit_refund_description(),
        ]);
        $this->assertDatabaseHas('invoices', [
            'id' => $order->invoice->id,
            'remark' => '*Pga. sen avbokning utgår full debitering men '.
                    'motsvarande summa/tid i krediter finns att använda i appen.',
        ]);

        Bus::assertDispatchedAfterResponse(SendNotificationJob::class);
    }

    public function testAuthenticatedUserCanNotCancelNotOwnScheduleWithApi(): void
    {
        Sanctum::actingAs($this->user, [TokenAbilityEnum::APIAccess()]);
        $response = $this->deleteJson('/api/v0/schedule-cleanings/1000/cancel');

        $response->assertStatus(Response::HTTP_NOT_FOUND)
            ->assertJson(fn (AssertableJson $json) => $json
                ->whereAllType([
                    'apiVersion' => 'string',
                    'error' => 'array',
                ]));
    }

    public function testNotAuthenticatedUserCanNotCancelScheduleWithApi(): void
    {
        $response = $this->deleteJson('/api/v0/schedule-cleanings/1/cancel');

        $response->assertStatus(Response::HTTP_UNAUTHORIZED)
            ->assertJson(fn (AssertableJson $json) => $json
                ->whereAllType([
                    'apiVersion' => 'string',
                    'error' => 'array',
                ]));
    }

    public function testAuthenticatedUserCanChangeScheduleWithApi(): void
    {
        Sanctum::actingAs($this->user, [TokenAbilityEnum::APIAccess()]);
        $subscription = Subscription::where('user_id', $this->user->id)->first();
        $scheduleCleaning = ScheduleCleaning::factory()
            ->forSubscription($subscription)
            ->forStatus(ScheduleCleaningStatusEnum::Pending())
            ->create();
        $response = $this->postJson(
            "/api/v0/schedule-cleanings/{$scheduleCleaning->id}/change",
            [
                'startAtChanged' => Carbon::now()->addDays(1)->toDateTimeString(),
                'endAtChanged' => Carbon::now()->addDays(2)->toDateTimeString(),
            ]
        );

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson(fn (AssertableJson $json) => $json
                ->whereAllType([
                    'apiVersion' => 'string',
                    'data' => 'array',
                ]));
    }

    public function testAuthenticatedUserCanNotChangeNotOwnScheduleWithApi(): void
    {
        Sanctum::actingAs($this->user, [TokenAbilityEnum::APIAccess()]);
        $response = $this->postJson(
            '/api/v0/schedule-cleanings/1000/change',
            [
                'startAtChanged' => Carbon::now()->addDays(1)->toDateTimeString(),
                'endAtChanged' => Carbon::now()->addDays(2)->toDateTimeString(),
            ]
        );

        $response->assertStatus(Response::HTTP_NOT_FOUND)
            ->assertJson(fn (AssertableJson $json) => $json
                ->whereAllType([
                    'apiVersion' => 'string',
                    'error' => 'array',
                ]));
    }

    public function testNotAuthenticatedUserCanNotChangeScheduleWithApi(): void
    {
        $response = $this->postJson('/api/v0/schedule-cleanings/1/change', []);

        $response->assertStatus(Response::HTTP_UNAUTHORIZED)
            ->assertJson(fn (AssertableJson $json) => $json
                ->whereAllType([
                    'apiVersion' => 'string',
                    'error' => 'array',
                ]));
    }
}
