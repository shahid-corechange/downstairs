<?php

namespace Tests\Portal\Operation;

use App\DTOs\ScheduleCleaning\ScheduleCleaningResponseDTO;
use App\Enums\Credit\CreditTransactionTypeEnum;
use App\Enums\Discount\CustomerDiscountTypeEnum;
use App\Enums\GlobalSetting\GlobalSettingEnum;
use App\Enums\MembershipTypeEnum;
use App\Enums\Order\OrderStatusEnum;
use App\Enums\Product\ProductUnitEnum;
use App\Enums\ScheduleCleaning\CleaningProductPaymentMethodEnum;
use App\Enums\ScheduleCleaning\ScheduleCleaningChangeStatusEnum;
use App\Enums\ScheduleCleaning\ScheduleCleaningStatusEnum;
use App\Enums\ScheduleEmployee\ScheduleEmployeeStatusEnum;
use App\Enums\Subscription\SubscriptionRefillSequenceEnum;
use App\Enums\VatNumbersEnum;
use App\Jobs\SendNotificationJob;
use App\Models\BlockDay;
use App\Models\Credit;
use App\Models\CustomerDiscount;
use App\Models\Order;
use App\Models\OrderRow;
use App\Models\Product;
use App\Models\ScheduleCleaning;
use App\Models\Team;
use App\Services\CreditService;
use Bus;
use Carbon\Carbon;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\Testing\Fluent\AssertableJson;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ScheduleTest extends TestCase
{
    public function testAdminCanAccessSchedules(): void
    {
        $timezone = $this->admin->info->timezone;
        $startAt = Carbon::now($timezone)->startOfWeek(Carbon::MONDAY)->utc();
        $endAt = Carbon::now($timezone)->endOfWeek(Carbon::SUNDAY)->utc();

        $data = ScheduleCleaning::where('start_at', '>=', $startAt->clone()->subDays(1))
            ->where('end_at', '<=', $endAt->clone()->addDays(1))
            ->get();

        $filteredData = Collection::make($data)
            ->filter(fn (ScheduleCleaning $schedule) => ($schedule->start_at->gte($startAt) &&
            $schedule->end_at->lte($endAt)) ||
                ($schedule->start_at->lt($startAt) && $schedule->end_at->gte($startAt)) ||
                ($schedule->start_at->lte($endAt) && $schedule->end_at->gt($endAt)));

        $this->actingAs($this->admin)
            ->get('/schedules')
            ->assertInertia(fn (Assert $page) => $page
                ->component('Schedule/Overview/index')
                ->has('schedules', $filteredData->count())
                ->has('schedules.0', fn (Assert $page) => $page
                    ->has('id')
                    ->has('isFixed')
                    ->has('startAt')
                    ->has('endAt')
                    ->has('quarters')
                    ->etc()
                    ->has('subscription')
                    ->has('customer', fn (Assert $page) => $page
                        ->has('membershipType')
                        ->etc())
                    ->has('team', fn (Assert $page) => $page
                        ->has('id')
                        ->has('name')
                        ->etc())));
    }

    public function testCustomerCanNotAccessSchedules(): void
    {
        $this->actingAs($this->user)
            ->get('/schedules')
            ->assertInertia(fn (Assert $page) => $page
                ->component('Error/index')
                ->where('code', '404'));
    }

    public function testCanFilterSchedules(): void
    {
        $timezone = $this->admin->info->timezone;
        $startAt = Carbon::now($timezone)->startOfWeek(Carbon::MONDAY)->utc();
        $endAt = Carbon::now($timezone)->endOfWeek(Carbon::SUNDAY)->utc();
        $data = ScheduleCleaning::first();

        $data->update([
            'start_at' => $startAt,
            'end_at' => $endAt,
            'status' => ScheduleCleaningStatusEnum::Booked(),
        ]);

        $this->actingAs($this->admin)
            ->get("/schedules?id.eq={$data->id}")
            ->assertInertia(fn (Assert $page) => $page
                ->component('Schedule/Overview/index')
                ->has('schedules', 1)
                ->has('schedules.0', fn (Assert $page) => $page
                    ->where('id', $data->id)
                    ->where('isFixed', $data->is_fixed)
                    ->where('quarters', $data->quarters)
                    ->has('startAt')
                    ->has('endAt')
                    ->where('status', $data->status)
                    ->etc()
                    ->has('subscription')
                    ->has('customer', fn (Assert $page) => $page
                        ->where('membershipType', $data->subscription->customer->membership_type)
                        ->etc())
                    ->has('team', fn (Assert $page) => $page
                        ->where('id', $data->team->id)
                        ->where('name', $data->team->name)
                        ->etc())));
    }

    public function testAdminCanAccessSchedulesJson(): void
    {
        $response = $this->actingAs($this->admin)
            ->get('/schedules/json');
        $keys = array_keys(
            ScheduleCleaningResponseDTO::from(ScheduleCleaning::first())->toArray()
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

    public function testAdminCanAccessScheduleJson(): void
    {
        $response = $this->actingAs($this->admin)
            ->get('/schedules/1/json');
        $keys = array_keys(
            ScheduleCleaningResponseDTO::from(ScheduleCleaning::first())->toArray()
        );

        $response->assertStatus(200);

        $response->assertJsonStructure([
            'data' => $keys,
            'meta' => [
                'etag',
            ],
        ]);
    }

    public function testCanCancelScheduleWithRefund(): void
    {
        OrderRow::whereNotNull('id')->delete();
        Order::whereNotNull('id')->forceDelete();

        $refundTime = get_setting(GlobalSettingEnum::CreditRefundTimeWindow(), 72);

        $schedule = ScheduleCleaning::future()->first();

        $schedule->update([
            'start_at' => now()->addHours($refundTime),
            'end_at' => now()->addHours($refundTime + 1),
        ]);
        $schedule->changeRequest()->create([
            'start_at_changed' => $schedule->start_at->addHours(1),
            'end_at_changed' => $schedule->end_at->addHours(1),
            'status' => ScheduleCleaningChangeStatusEnum::Pending(),
        ]);
        $schedule->products()->create([
            'product_id' => 1,
            'quantity' => 1,
            'price' => 100,
            'discount_percentage' => 100,
            'payment_method' => CleaningProductPaymentMethodEnum::Credit(),
        ]);
        $data = [
            'refund' => true,
        ];
        $discount = CustomerDiscount::getCurrentDiscountByUser(
            $schedule->subscription->user_id,
            CustomerDiscountTypeEnum::Cleaning(),
        );

        $this->actingAs($this->admin)
            ->postJson("/schedules/$schedule->id/cancel", $data)
            ->assertStatus(Response::HTTP_OK)
            ->assertJson(fn (AssertableJson $json) => $json
                ->whereAllType([
                    'message' => 'string',
                    'data' => 'array',
                ])
                ->where('message', __('schedule cleaning canceled successfully')));

        $this->assertDatabaseHas('schedule_cleanings', [
            'id' => $schedule->id,
            'status' => ScheduleCleaningStatusEnum::Cancel(),
        ]);

        $this->assertDatabaseHas('schedule_employees', [
            'scheduleable_type' => ScheduleCleaning::class,
            'scheduleable_id' => $schedule->id,
            'status' => ScheduleEmployeeStatusEnum::Cancel(),
            'description' => __('schedule canceled by admin'),
        ]);

        $this->assertDatabaseHas('orders', [
            'user_id' => $schedule->subscription->user_id,
            'customer_id' => $schedule->subscription->customer_id,
            'service_id' => $schedule->subscription->service_id,
            'subscription_id' => $schedule->subscription_id,
            'status' => OrderStatusEnum::Draft(),
        ]);

        $isPrivate = $schedule->customer->membership_type === MembershipTypeEnum::Private();
        $this->assertDatabaseHas('order_rows', [
            'description' => $schedule->subscription->service->name.get_credit_refund_description(),
            'fortnox_article_id' => $schedule->subscription->service->fortnox_article_id,
            'quantity' => $schedule->subscription->quarters,
            'unit' => ProductUnitEnum::Piece(),
            'price' => $schedule->subscription->service->price,
            'discount_percentage' => $discount?->value ?? 0,
            'vat' => VatNumbersEnum::TwentyFive(),
            'has_rut' => $isPrivate ? $schedule->subscription->service->has_rut : false,
        ]);

        // Assert credit
        $creditService = new CreditService();
        $amount = $creditService->calculateRefund($schedule);
        $this->assertDatabaseHas('credits', [
            'user_id' => $schedule->subscription->user_id,
            'schedule_cleaning_id' => $schedule->id,
            'type' => CreditTransactionTypeEnum::Refund(),
            'initial_amount' => $amount,
            'remaining_amount' => $amount,
        ]);

        $this->assertDatabaseHas('credit_transactions', [
            'user_id' => $schedule->subscription->user_id,
            'schedule_cleaning_id' => $schedule->id,
            'type' => CreditTransactionTypeEnum::Refund(),
            'total_amount' => $amount,
        ]);

        $this->assertDatabaseHas('schedule_cleaning_change_requests', [
            'schedule_cleaning_id' => $schedule->id,
            'status' => ScheduleCleaningChangeStatusEnum::Canceled(),
            'causer_id' => $this->admin->id,
            'original_start_at' => $schedule->start_at,
            'original_end_at' => $schedule->end_at,
        ]);

        $product = Product::find(1);
        $this->assertDatabaseHas('credit_transactions', [
            'user_id' => $schedule->subscription->user_id,
            'schedule_cleaning_id' => $schedule->id,
            'type' => CreditTransactionTypeEnum::Refund(),
            'total_amount' => $product->credit_price,
            'issuer_id' => $this->admin->id,
        ]);

        $order = $schedule->order;
        $this->assertDatabaseHas('order_rows', [
            'order_id' => $order->id,
            'description' => $schedule->subscription->service->name.get_credit_refund_description(),
        ]);
        $this->assertDatabaseHas('invoices', [
            'id' => $order->invoice->id,
            'remark' => '*Pga. sen avbokning utgår full debitering men '.
                    'motsvarande summa/tid i krediter finns att använda i appen.',
        ]);

        Bus::assertDispatchedAfterResponse(SendNotificationJob::class);
    }

    public function testCanNotCancelScheduleWithRefundIfNotWithinTimeWindow(): void
    {
        OrderRow::whereNotNull('id')->delete();
        Order::whereNotNull('id')->forceDelete();

        $refundTime = get_setting(GlobalSettingEnum::CreditRefundTimeWindow(), 72);

        $schedule = ScheduleCleaning::future()->first();
        $schedule->update([
            'start_at' => now()->addHours($refundTime + 1),
            'end_at' => now()->addHours($refundTime + 2),
        ]);
        $data = [
            'refund' => true,
        ];

        $this->actingAs($this->admin)
            ->postJson("/schedules/$schedule->id/cancel", $data)
            ->assertStatus(Response::HTTP_BAD_REQUEST)
            ->assertJson(fn (AssertableJson $json) => $json
                ->whereAllType([
                    'error' => 'array',
                ])
                ->where('error.message', __('schedule cannot be refunded')));
    }

    public function testCanNotCancelScheduleWithRefund(): void
    {
        $schedule = ScheduleCleaning::where('status', ScheduleCleaningStatusEnum::Done())
            ->first();
        $data = [
            'refund' => true,
        ];

        $this->actingAs($this->admin)
            ->postJson("/schedules/$schedule->id/cancel", $data)
            ->assertStatus(Response::HTTP_BAD_REQUEST)
            ->assertJson(fn (AssertableJson $json) => $json
                ->whereAllType([
                    'error' => 'array',
                ])
                ->where('error.message', __('failed to cancel schedule due to schedule status')));
    }

    public function testCanCancelScheduleWithoutRefund(): void
    {
        OrderRow::whereNotNull('id')->delete();
        Order::whereNotNull('id')->forceDelete();
        $schedule = ScheduleCleaning::future()->first();
        $schedule->changeRequest()->create([
            'start_at_changed' => $schedule->start_at->addHours(1),
            'end_at_changed' => $schedule->end_at->addHours(1),
            'status' => ScheduleCleaningChangeStatusEnum::Pending(),
        ]);
        $data = [
            'refund' => false,
        ];
        $schedule->products()->create([
            'product_id' => 1,
            'quantity' => 1,
            'price' => 100,
            'discount_percentage' => 100,
            'payment_method' => CleaningProductPaymentMethodEnum::Credit(),
        ]);

        $this->actingAs($this->admin)
            ->postJson("/schedules/$schedule->id/cancel", $data)
            ->assertStatus(Response::HTTP_OK)
            ->assertJson(fn (AssertableJson $json) => $json
                ->whereAllType([
                    'message' => 'string',
                    'data' => 'array',
                ])
                ->where('message', __('schedule cleaning canceled successfully')));

        $this->assertDatabaseHas('schedule_cleanings', [
            'id' => $schedule->id,
            'status' => ScheduleCleaningStatusEnum::Cancel(),
        ]);

        $this->assertDatabaseHas('schedule_employees', [
            'scheduleable_type' => ScheduleCleaning::class,
            'scheduleable_id' => $schedule->id,
            'status' => ScheduleCleaningStatusEnum::Cancel(),
            'description' => __('schedule canceled by admin'),
        ]);

        $this->assertDatabaseMissing('orders', [
            'user_id' => $schedule->subscription->user_id,
            'customer_id' => $schedule->subscription->customer_id,
            'service_id' => $schedule->subscription->service_id,
            'subscription_id' => $schedule->subscription_id,
            'status' => OrderStatusEnum::Draft(),
        ]);

        $isPrivate = $schedule->customer->membership_type === MembershipTypeEnum::Private();
        $this->assertDatabaseMissing('order_rows', [
            'description' => $schedule->subscription->service->name.get_credit_refund_description(),
            'fortnox_article_id' => $schedule->subscription->service->fortnox_article_id,
            'quantity' => $schedule->subscription->quarters,
            'unit' => ProductUnitEnum::Piece(),
            'price' => $schedule->subscription->service->price,
            'discount_percentage' => 0,
            'vat' => VatNumbersEnum::TwentyFive(),
            'has_rut' => $isPrivate ? $schedule->subscription->service->has_rut : false,
        ]);

        $this->assertDatabaseHas('schedule_cleaning_change_requests', [
            'schedule_cleaning_id' => $schedule->id,
            'status' => ScheduleCleaningChangeStatusEnum::Canceled(),
            'causer_id' => $this->admin->id,
            'original_start_at' => $schedule->start_at,
            'original_end_at' => $schedule->end_at,
        ]);

        $product = Product::find(1);
        $this->assertDatabaseHas('credit_transactions', [
            'user_id' => $schedule->subscription->user_id,
            'schedule_cleaning_id' => $schedule->id,
            'type' => CreditTransactionTypeEnum::Refund(),
            'total_amount' => $product->credit_price,
            'issuer_id' => $this->admin->id,
        ]);

        $this->assertDatabaseMissing('orders', [
            'orderable_type' => ScheduleCleaning::class,
            'orderable_id' => $schedule->id,
        ]);

        Bus::assertDispatchedAfterResponse(SendNotificationJob::class);
    }

    public function testCanUpdateSchedule(): void
    {
        $schedule = ScheduleCleaning::future()->first();
        $schedule->products()->whereNot('product_id', 1)->delete();
        ScheduleCleaning::whereNot('id', $schedule->id)->delete();
        $schedule->update([
            'start_at' => now()->addDays(300),
            'end_at' => now()->addDays(300)->addHour(),
        ]);
        $schedule->changeRequest()->create([
            'start_at_changed' => $schedule->start_at->addHours(1),
            'end_at_changed' => $schedule->end_at->addHours(1),
            'status' => ScheduleCleaningChangeStatusEnum::Pending(),
        ]);
        $schedule->products()->create([
            'product_id' => 1,
            'quantity' => 1,
            'price' => 100,
            'discount_percentage' => 100,
            'payment_method' => CleaningProductPaymentMethodEnum::Credit(),
        ]);

        $data = [
            'quarters' => 4,
            'note' => 'test',
            'removeAddOns' => [1],
            'newAddOns' => [
                [
                    'addonId' => 2,
                    'quantity' => 1,
                    'useCredit' => true,
                ],
                [
                    'addonId' => 3,
                    'quantity' => 1,
                    'useCredit' => true,
                ],
                [
                    'addonId' => 4,
                    'quantity' => 1,
                    'useCredit' => false,
                ],
            ],
            'startAt' => $schedule->start_at->addHours(1)->format('Y-m-d H:i:s'),
            'endAt' => $schedule->end_at->addHours(1)->format('Y-m-d H:i:s'),
        ];

        $endTime = calculate_end_time(
            $schedule->start_at,
            calculate_calendar_quarters(
                $data['quarters'],
                $schedule->scheduleEmployees->count()
            ),
            format: 'Y-m-d H:i:s'
        );

        $this->actingAs($this->admin)
            ->patchJson("/schedules/$schedule->id", $data)
            ->assertStatus(Response::HTTP_OK)
            ->assertJson(fn (AssertableJson $json) => $json
                ->whereAllType([
                    'message' => 'string',
                    'data' => 'array',
                ])
                ->where('message', __('schedule cleaning updated successfully')));

        $this->assertDatabaseHas('schedule_cleanings', [
            'id' => $schedule->id,
            'quarters' => $data['quarters'],
            'note->note' => $data['note'],
            'start_at' => $data['startAt'],
            'end_at' => $data['endAt'],
        ]);

        $this->assertDatabaseMissing('schedule_cleaning_products', [
            'schedule_cleaning_id' => $schedule->id,
            'product_id' => 1,
        ]);

        $this->assertDatabaseHas('schedule_cleaning_products', [
            'schedule_cleaning_id' => $schedule->id,
            'product_id' => 2,
            'quantity' => 1,
            'discount_percentage' => 100,
            'payment_method' => CleaningProductPaymentMethodEnum::Credit(),
        ]);

        $this->assertDatabaseHas('schedule_cleaning_products', [
            'schedule_cleaning_id' => $schedule->id,
            'product_id' => 3,
            'quantity' => 1,
            'discount_percentage' => 100,
            'payment_method' => CleaningProductPaymentMethodEnum::Credit(),
        ]);

        $this->assertDatabaseHas('schedule_cleaning_products', [
            'schedule_cleaning_id' => $schedule->id,
            'product_id' => 4,
            'quantity' => 1,
            'discount_percentage' => 0,
            'payment_method' => CleaningProductPaymentMethodEnum::Invoice(),
        ]);

        foreach ($schedule->products as $product) {
            $this->assertDatabaseHas('credit_transactions', [
                'user_id' => $schedule->subscription->user_id,
                'schedule_cleaning_id' => $schedule->id,
                'type' => CreditTransactionTypeEnum::Payment(),
                'total_amount' => $product->product->credit_price,
            ]);
        }

        $product = Product::find(1);
        $this->assertDatabaseHas('credit_transactions', [
            'user_id' => $schedule->subscription->user_id,
            'schedule_cleaning_id' => $schedule->id,
            'type' => CreditTransactionTypeEnum::Refund(),
            'total_amount' => $product->credit_price,
            'issuer_id' => $this->admin->id,
        ]);

        $changeRequest = $schedule->changeRequest;
        $timesMatch = $changeRequest->isTimeMatch($data['startAt'], $data['endAt']);
        $changeRequestStatus = $timesMatch ? ScheduleCleaningChangeStatusEnum::Approved() :
            ScheduleCleaningChangeStatusEnum::Handled();
        $this->assertDatabaseHas('schedule_cleaning_change_requests', [
            'schedule_cleaning_id' => $schedule->id,
            'status' => $changeRequestStatus,
            'causer_id' => $this->admin->id,
            'original_start_at' => $schedule->start_at,
            'original_end_at' => $schedule->end_at,
        ]);

        Bus::assertDispatchedAfterResponse(SendNotificationJob::class);
    }

    public function testCanNotUpdateScheduleDueToScheduleStatus(): void
    {
        $schedule = ScheduleCleaning::first();
        $schedule->update([
            'status' => ScheduleCleaningStatusEnum::Done(),
        ]);

        $data = [
            'startAt' => $schedule->start_at->addHours(1)->format('Y-m-d H:i:s'),
            'endAt' => $schedule->end_at->addHours(1)->format('Y-m-d H:i:s'),
        ];

        $this->actingAs($this->admin)
            ->patchJson("/schedules/$schedule->id", $data)
            ->assertStatus(Response::HTTP_BAD_REQUEST)
            ->assertJson(fn (AssertableJson $json) => $json
                ->whereAllType([
                    'error' => 'array',
                ])
                ->where('error.message', __('failed to update schedule due to schedule status')));
    }

    public function testCanNotUpdateScheduleMoreThanOneYear(): void
    {
        $schedule = ScheduleCleaning::first();
        $schedule->update([
            'start_at' => now()->addYears(2),
            'end_at' => now()->addYears(2)->addHour(),
            'status' => ScheduleCleaningStatusEnum::Booked(),
        ]);
        ScheduleCleaning::whereNot('id', $schedule->id)->forceDelete();

        $data = [
            'startAt' => now()->addHours(1)->format('Y-m-d H:i:s'),
            'endAt' => now()->addHours(2)->format('Y-m-d H:i:s'),
        ];

        $refillSequence = get_setting(
            GlobalSettingEnum::SubscriptionRefillSequence(),
            config('downstairs.subscription.refillSequence')
        );
        $refillSequences = SubscriptionRefillSequenceEnum::options();
        $time = array_search($refillSequence, $refillSequences);

        $this->actingAs($this->admin)
            ->patchJson("/schedules/$schedule->id", $data)
            ->assertStatus(Response::HTTP_BAD_REQUEST)
            ->assertJson(fn (AssertableJson $json) => $json
                ->whereAllType([
                    'error' => 'array',
                ])
                ->where('error.message', __('failed to update schedule a certain time ahead', [
                    'time' => __($time),
                ])));
    }

    public function testCanNotUpdateScheduleIfBlockDay(): void
    {
        $schedule = ScheduleCleaning::future()->first();
        $schedule->update([
            'start_at' => now()->addDays(300),
            'end_at' => now()->addDays(300)->addHour(),
        ]);

        BlockDay::create([
            'block_date' => $schedule->start_at->format('Y-m-d'),
            'start_block_time' => $schedule->start_at->copy()->startOfDay()->format('H:i:s'),
            'end_block_time' => $schedule->start_at->copy()->endOfDay()->format('H:i:s'),
        ]);

        $data = [
            'keyInformation' => 'test',
            'quarters' => 200,
            'note' => 'test',
            'removeAddOns' => [],
            'newAddOns' => [],
            'startAt' => $schedule->start_at->addHours(1)->format('Y-m-d H:i:s'),
            'endAt' => $schedule->end_at->addHours(1)->format('Y-m-d H:i:s'),
        ];

        $this->actingAs($this->admin)
            ->patchJson("/schedules/$schedule->id", $data)
            ->assertStatus(Response::HTTP_BAD_REQUEST)
            ->assertJson(fn (AssertableJson $json) => $json
                ->whereAllType([
                    'error' => 'array',
                ])
                ->where('error.message', __('failed to update schedule due to block day')));
    }

    public function testCanNotUpdateScheduleIfConfilct(): void
    {
        $schedule = ScheduleCleaning::active()->first();
        $schedule2 = ScheduleCleaning::active()
            ->where('id', '!=', $schedule->id)
            ->first();
        $startAt = $schedule->end_at->copy()->subMinutes(1);
        $schedule2->update(['start_at' => $startAt]);
        $data = [
            'keyInformation' => 'test',
            'quarters' => 200,
            'note' => 'test',
            'removeAddOns' => [],
            'newAddOns' => [],
        ];

        $this->actingAs($this->admin)
            ->patchJson("/schedules/$schedule->id", $data)
            ->assertStatus(Response::HTTP_CONFLICT)
            ->assertJson(fn (AssertableJson $json) => $json
                ->whereAllType([
                    'error' => 'array',
                    'error.errors' => 'array',
                ])
                ->where('error.message', __('this action causes conflict with other schedules')));
    }

    public function testCanNotUpdateScheduleIfInsufficientCredit(): void
    {
        $schedule = ScheduleCleaning::future()->first();
        $schedule->update([
            'start_at' => now()->addDays(300),
            'end_at' => now()->addDays(300)->addHour(),
        ]);
        Credit::where('user_id', $schedule->subscription->user_id)->delete();

        $data = [
            'keyInformation' => 'test',
            'quarters' => 200,
            'note' => 'test',
            'removeAddOns' => [],
            'newAddOns' => [
                [
                    'addonId' => 1,
                    'quantity' => 1,
                    'useCredit' => true,
                ],
                [
                    'addonId' => 2,
                    'quantity' => 1,
                    'useCredit' => true,
                ],
            ],
        ];

        $this->actingAs($this->admin)
            ->patchJson("/schedules/$schedule->id", $data)
            ->assertStatus(Response::HTTP_BAD_REQUEST)
            ->assertJson(fn (AssertableJson $json) => $json
                ->whereAllType([
                    'error' => 'array',
                ])
                ->where('error.message', __('insufficient credits')));
    }

    public function testCanRescheduleWithNotification(): void
    {
        $schedule = ScheduleCleaning::future()->first();
        ScheduleCleaning::whereNot('id', $schedule->id)->delete();
        $this->team->users()->attach($this->worker);
        $data = [
            'teamId' => $this->team->id,
            'startAt' => now()->addYear()->format('Y-m-d H:i:s'),
            'isNotify' => true,
        ];
        $schedule->changeRequest()->create([
            'start_at_changed' => $schedule->start_at->addHours(1),
            'end_at_changed' => $schedule->end_at->addHours(1),
            'status' => ScheduleCleaningChangeStatusEnum::Pending(),
        ]);

        $workers = $data['teamId'] !== $schedule->team_id ?
            Team::find($data['teamId'])->users :
            $schedule->scheduleEmployees;

        $startAt = Carbon::parse($data['startAt']);
        $endAt = Carbon::parse(calculate_end_time(
            $data['startAt'],
            calculate_calendar_quarters(
                $schedule->quarters,
                $workers->count(),
            ),
            format: 'Y-m-d H:i:s'
        ));

        $this->actingAs($this->admin)
            ->postJson("/schedules/$schedule->id/reschedule", $data)
            ->assertStatus(Response::HTTP_OK)
            ->assertJson(fn (AssertableJson $json) => $json
                ->whereAllType([
                    'message' => 'string',
                    'data' => 'array',
                ])
                ->where('message', __('reschedule successfully')));

        $this->assertDatabaseHas('schedule_cleanings', [
            'id' => $schedule->id,
            'team_id' => $this->team->id,
            'start_at' => $data['startAt'],
        ]);

        $this->assertDatabaseHas('schedule_employees', [
            'scheduleable_type' => ScheduleCleaning::class,
            'scheduleable_id' => $schedule->id,
            'status' => ScheduleCleaningStatusEnum::Pending(),
        ]);

        $changeRequest = $schedule->changeRequest;
        $timesMatch = $changeRequest->isTimeMatch($startAt, $endAt);
        $changeRequestStatus = $timesMatch ? ScheduleCleaningChangeStatusEnum::Approved() :
            ScheduleCleaningChangeStatusEnum::Handled();
        $this->assertDatabaseHas('schedule_cleaning_change_requests', [
            'schedule_cleaning_id' => $schedule->id,
            'status' => $changeRequestStatus,
            'causer_id' => $this->admin->id,
            'original_start_at' => $schedule->start_at,
            'original_end_at' => $schedule->end_at,
        ]);

        Bus::assertDispatchedAfterResponse(SendNotificationJob::class);
    }

    public function testCanRescheduleWithoutNotification(): void
    {
        $schedule = ScheduleCleaning::future()->first();
        $this->team->users()->attach($this->worker);
        $data = [
            'teamId' => $this->team->id,
            'startAt' => now()->addYear()->format('Y-m-d H:i:s'),
            'isNotify' => false,
        ];
        $schedule->changeRequest()->create([
            'start_at_changed' => $schedule->start_at->addHours(1),
            'end_at_changed' => $schedule->end_at->addHours(1),
            'status' => ScheduleCleaningChangeStatusEnum::Pending(),
        ]);

        $workers = $data['teamId'] !== $schedule->team_id ?
            Team::find($data['teamId'])->users :
            $schedule->scheduleEmployees;

        $startAt = Carbon::parse($data['startAt']);
        $endAt = Carbon::parse(calculate_end_time(
            $data['startAt'],
            calculate_calendar_quarters(
                $schedule->quarters,
                $workers->count(),
            ),
            format: 'Y-m-d H:i:s'
        ));

        $this->actingAs($this->admin)
            ->postJson("/schedules/$schedule->id/reschedule", $data)
            ->assertStatus(Response::HTTP_OK)
            ->assertJson(fn (AssertableJson $json) => $json
                ->whereAllType([
                    'message' => 'string',
                    'data' => 'array',
                ])
                ->where('message', __('reschedule successfully')));

        $this->assertDatabaseHas('schedule_cleanings', [
            'id' => $schedule->id,
            'team_id' => $this->team->id,
            'start_at' => $data['startAt'],
        ]);

        $this->assertDatabaseHas('schedule_employees', [
            'scheduleable_type' => ScheduleCleaning::class,
            'scheduleable_id' => $schedule->id,
            'status' => ScheduleCleaningStatusEnum::Pending(),
        ]);

        $changeRequest = $schedule->changeRequest;
        $timesMatch = $changeRequest->isTimeMatch($startAt, $endAt);
        $changeRequestStatus = $timesMatch ? ScheduleCleaningChangeStatusEnum::Approved() :
            ScheduleCleaningChangeStatusEnum::Handled();
        $this->assertDatabaseHas('schedule_cleaning_change_requests', [
            'schedule_cleaning_id' => $schedule->id,
            'status' => $changeRequestStatus,
            'causer_id' => $this->admin->id,
            'original_start_at' => $schedule->start_at,
            'original_end_at' => $schedule->end_at,
        ]);

        Bus::assertNotDispatchedAfterResponse(SendNotificationJob::class);
    }

    public function testCanNotRescheduleIfNotBookedAndNotProgress(): void
    {
        $schedule = ScheduleCleaning::whereNotIn(
            'status',
            [ScheduleCleaningStatusEnum::Booked(), ScheduleCleaningStatusEnum::Progress()]
        )->first();
        $data = [
            'teamId' => $schedule->team_id,
            'startAt' => now()->addYear()->format('Y-m-d H:i:s'),
            'isNotify' => true,
        ];

        $this->actingAs($this->admin)
            ->postJson("/schedules/$schedule->id/reschedule", $data)
            ->assertStatus(Response::HTTP_BAD_REQUEST)
            ->assertJson(fn (AssertableJson $json) => $json
                ->whereAllType([
                    'error' => 'array',
                ])
                ->where('error.message', __('failed to reschedule due to schedule status')));
    }

    public function testCanNotRescheduleIfScheduleMoreThanOneYear(): void
    {
        $schedule = ScheduleCleaning::first();
        $schedule->update([
            'start_at' => now()->addYears(2),
            'end_at' => now()->addYears(2)->addHour(),
            'status' => ScheduleCleaningStatusEnum::Booked(),
        ]);
        ScheduleCleaning::whereNot('id', $schedule->id)->forceDelete();

        $data = [
            'teamId' => $schedule->team_id,
            'startAt' => now()->addHour()->format('Y-m-d H:i:s'),
            'isNotify' => true,
        ];
        $refillSequence = get_setting(
            GlobalSettingEnum::SubscriptionRefillSequence(),
            config('downstairs.subscription.refillSequence')
        );
        $refillSequences = SubscriptionRefillSequenceEnum::options();
        $time = array_search($refillSequence, $refillSequences);

        $this->actingAs($this->admin)
            ->postJson("/schedules/$schedule->id/reschedule", $data)
            ->assertStatus(Response::HTTP_BAD_REQUEST)
            ->assertJson(fn (AssertableJson $json) => $json
                ->whereAllType([
                    'error' => 'array',
                ])
                ->where('error.message', __('failed to reschedule a certain time ahead', [
                    'time' => __($time),
                ])));
    }

    public function testCanNotRescheduleScheduleToMoreThanOneYear(): void
    {
        $schedule = ScheduleCleaning::first();
        $schedule->update([
            'start_at' => now()->addHour(),
            'end_at' => now()->addHours(2),
            'status' => ScheduleCleaningStatusEnum::Booked(),
        ]);
        ScheduleCleaning::whereNot('id', $schedule->id)->forceDelete();

        $data = [
            'teamId' => $schedule->team_id,
            'startAt' => $schedule->start_at->addYear()->addDay()->format('Y-m-d H:i:s'),
            'endAt' => $schedule->end_at->addYear()->addDay()->format('Y-m-d H:i:s'),
            'isNotify' => true,
        ];
        $refillSequence = get_setting(
            GlobalSettingEnum::SubscriptionRefillSequence(),
            config('downstairs.subscription.refillSequence')
        );
        $refillSequences = SubscriptionRefillSequenceEnum::options();
        $time = array_search($refillSequence, $refillSequences);

        $this->actingAs($this->admin)
            ->postJson("/schedules/$schedule->id/reschedule", $data)
            ->assertStatus(Response::HTTP_BAD_REQUEST)
            ->assertJson(fn (AssertableJson $json) => $json
                ->whereAllType([
                    'error' => 'array',
                ])
                ->where('error.message', __('failed to reschedule to a certain time ahead', [
                    'time' => __($time),
                ])));
    }

    public function testCanNotRescheduleIfConflict(): void
    {
        $schedule = ScheduleCleaning::active()->first();
        $schedule2 = ScheduleCleaning::active()
            ->where('id', '!=', $schedule->id)
            ->first();
        $startAt = $schedule->end_at->copy()->subMinutes(1);
        $schedule2->update(['start_at' => $startAt]);
        $data = [
            'teamId' => $schedule->team_id,
            'startAt' => $schedule->start_at->format('Y-m-d H:i:s'),
            'isNotify' => true,
        ];

        $this->actingAs($this->admin)
            ->postJson("/schedules/$schedule->id/reschedule", $data)
            ->assertStatus(Response::HTTP_CONFLICT)
            ->assertJson(fn (AssertableJson $json) => $json
                ->whereAllType([
                    'error' => 'array',
                    'error.errors' => 'array',
                ])
                ->where('error.message', __('schedule collision')));
    }

    public function testCanNotRescheduleIfInBlockDays(): void
    {
        $tomorrow = Carbon::tomorrow();
        $schedule = ScheduleCleaning::where('status', ScheduleCleaningStatusEnum::Booked())
            ->first();
        BlockDay::create([
            'block_date' => $tomorrow->format('Y-m-d'),
            'start_block_time' => $tomorrow->startOfDay()->format('H:i:s'),
            'end_block_time' => $tomorrow->endOfDay()->format('H:i:s'),
        ]);

        $data = [
            'teamId' => $schedule->team_id,
            'startAt' => $tomorrow->format('Y-m-d H:i:s'),
            'isNotify' => true,
        ];
        $this->actingAs($this->admin)
            ->postJson("/schedules/$schedule->id/reschedule", $data)
            ->assertStatus(Response::HTTP_BAD_REQUEST)
            ->assertJson(fn (AssertableJson $json) => $json
                ->whereAllType([
                    'error' => 'array',
                ])
                ->where('error.message', __('failed to reschedule due to block day')));
    }

    public function testCanBulkCancelSchedule(): void
    {
        $schedule = ScheduleCleaning::future()->first();
        $schedule2 = ScheduleCleaning::future()
            ->where('subscription_id', $schedule->subscription_id)
            ->where('id', '!=', $schedule->id)
            ->first();
        $data = [
            'userId' => $schedule->subscription->user_id,
            'scheduleIds' => [$schedule->id, $schedule2->id],
        ];

        $this->actingAs($this->admin)
            ->postJson('/schedules/bulk-cancel', $data)
            ->assertStatus(Response::HTTP_OK)
            ->assertJson(fn (AssertableJson $json) => $json
                ->whereAllType([
                    'message' => 'string',
                    'data' => 'array',
                ])
                ->where('message', __('schedule cleaning canceled successfully')));

        $this->assertDatabaseHas('schedule_cleanings', [
            'id' => $schedule->id,
            'status' => ScheduleCleaningStatusEnum::Cancel(),
        ]);

        $this->assertDatabaseHas('schedule_cleanings', [
            'id' => $schedule2->id,
            'status' => ScheduleCleaningStatusEnum::Cancel(),
        ]);

        $this->assertDatabaseHas('schedule_employees', [
            'scheduleable_type' => ScheduleCleaning::class,
            'scheduleable_id' => $schedule->id,
            'status' => ScheduleCleaningStatusEnum::Cancel(),
            'description' => __('schedule canceled by admin'),
        ]);

        $this->assertDatabaseHas('schedule_employees', [
            'scheduleable_type' => ScheduleCleaning::class,
            'scheduleable_id' => $schedule2->id,
            'status' => ScheduleCleaningStatusEnum::Cancel(),
            'description' => __('schedule canceled by admin'),
        ]);
    }

    public function testCanNotBulkCancelSchedule(): void
    {
        $data = [
            'userId' => $this->user->id,
            'scheduleIds' => [1, 2, 3],
        ];

        $this->actingAs($this->admin)
            ->postJson('/schedules/bulk-cancel', $data)
            ->assertStatus(Response::HTTP_BAD_REQUEST)
            ->assertJson(fn (AssertableJson $json) => $json
                ->whereAllType([
                    'error' => 'array',
                ])
                ->where('error.message', __('some schedules not owned by user')));
    }
}
