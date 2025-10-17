<?php

namespace Tests\Portal\Operation;

use App\DTOs\ScheduleEmployee\ScheduleEmployeeResponseDTO;
use App\DTOs\User\UserResponseDTO;
use App\Enums\Credit\CreditTransactionTypeEnum;
use App\Enums\Deviation\DeviationTypeEnum;
use App\Enums\ScheduleCleaning\CleaningProductPaymentMethodEnum;
use App\Enums\ScheduleCleaning\ScheduleCleaningChangeStatusEnum;
use App\Enums\ScheduleCleaning\ScheduleCleaningStatusEnum;
use App\Jobs\SendNotificationJob;
use App\Models\BlockDay;
use App\Models\CreditTransaction;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\ScheduleCleaning;
use App\Models\ScheduleEmployee;
use App\Models\User;
use App\Services\CreditService;
use App\Services\OrderService;
use Bus;
use Carbon\Carbon;
use Illuminate\Http\Response;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class ScheduleWorkerTest extends TestCase
{
    public function testCanAccessWorkerSchedulesJson(): void
    {
        $response = $this->actingAs($this->admin)
            ->get('/schedules/1/workers/json');
        $keys = array_keys(
            ScheduleEmployeeResponseDTO::from(ScheduleEmployee::first())->toArray()
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

    public function testCanAddWorker(): void
    {
        $schedule = ScheduleCleaning::where('status', ScheduleCleaningStatusEnum::Booked())
            ->first();
        $data = [
            'workerIds' => [$this->worker->id],
        ];
        $endTime = calculate_end_time(
            $schedule->start_at,
            calculate_calendar_quarters(
                $schedule->quarters,
                $schedule->scheduleEmployees->count() + count($data['workerIds'])
            ),
            format: 'Y-m-d H:i:s'
        );

        $this->actingAs($this->admin)
            ->postJson("/schedules/$schedule->id/workers", $data)
            ->assertStatus(Response::HTTP_CREATED)
            ->assertJson(fn (AssertableJson $json) => $json
                ->whereAllType([
                    'message' => 'string',
                    'data' => 'array',
                ])
                ->where('message', __('worker added to schedule successfully')));

        $this->assertDatabaseHas('schedule_employees', [
            'scheduleable_type' => ScheduleCleaning::class,
            'scheduleable_id' => $schedule->id,
            'user_id' => $this->worker->id,
        ]);

        $this->assertDatabaseHas('schedule_cleanings', [
            'id' => $schedule->id,
            'end_at' => $endTime,
        ]);
    }

    public function testCanNotAddWorker(): void
    {
        $schedule = ScheduleCleaning::whereNotIn(
            'status',
            [ScheduleCleaningStatusEnum::Booked(), ScheduleCleaningStatusEnum::Progress()]
        )->first();
        $data = [
            'workerIds' => [$this->worker->id],
        ];

        $this->actingAs($this->admin)
            ->postJson("/schedules/$schedule->id/workers", $data)
            ->assertStatus(Response::HTTP_BAD_REQUEST)
            ->assertJson(fn (AssertableJson $json) => $json
                ->whereAllType([
                    'error' => 'array',
                ])
                ->where('error.message', __('failed to add worker due to schedule status')));
    }

    public function testCanEnableWorker(): void
    {
        $schedule = ScheduleCleaning::where('status', ScheduleCleaningStatusEnum::Booked())
            ->first();
        $scheduleEmployee = $schedule->scheduleEmployees()->first();
        $scheduleEmployee->delete();
        ScheduleEmployee::whereNot('id', $scheduleEmployee->id)->forceDelete();

        $this->actingAs($this->admin)
            ->postJson("/schedules/{$schedule->id}/workers/{$scheduleEmployee->user_id}/enable")
            ->assertStatus(Response::HTTP_OK)
            ->assertJson(fn (AssertableJson $json) => $json
                ->whereAllType([
                    'message' => 'string',
                    'data' => 'array',
                ])
                ->where('message', __('worker enabled successfully')));

        $this->assertDatabaseHas('schedule_employees', [
            'id' => $scheduleEmployee->id,
            'deleted_at' => null,
        ]);

        Bus::assertDispatchedAfterResponse(SendNotificationJob::class);
    }

    public function testCanNotEnableIfScheduleNotBookedAndNotProgress(): void
    {
        $schedule = ScheduleCleaning::whereNotIn(
            'status',
            [ScheduleCleaningStatusEnum::Booked(), ScheduleCleaningStatusEnum::Progress()]
        )->first();

        $this->actingAs($this->admin)
            ->postJson("/schedules/{$schedule->id}/workers/{$this->worker->id}/enable")
            ->assertStatus(Response::HTTP_BAD_REQUEST)
            ->assertJson(fn (AssertableJson $json) => $json
                ->whereAllType([
                    'error' => 'array',
                ])
                ->where('error.message', __('failed to enable worker due to schedule status')));
    }

    public function testCanNotEnableIfNotWorker(): void
    {
        $schedule = ScheduleCleaning::where('status', ScheduleCleaningStatusEnum::Booked())
            ->first();

        $this->actingAs($this->admin)
            ->postJson("/schedules/{$schedule->id}/workers/{$this->user->id}/enable")
            ->assertStatus(Response::HTTP_BAD_REQUEST)
            ->assertJson(fn (AssertableJson $json) => $json
                ->whereAllType([
                    'error' => 'array',
                ])
                ->where('error.message', __('user not worker')));
    }

    public function testCanNotEnableIfWorkerNotInSchedule(): void
    {
        $schedule = ScheduleCleaning::where('status', ScheduleCleaningStatusEnum::Booked())
            ->first();

        $this->actingAs($this->admin)
            ->postJson("/schedules/{$schedule->id}/workers/{$this->worker->id}/enable")
            ->assertStatus(Response::HTTP_BAD_REQUEST)
            ->assertJson(fn (AssertableJson $json) => $json
                ->whereAllType([
                    'error' => 'array',
                ])
                ->where('error.message', __('worker not in schedule')));
    }

    public function testCanDisableWorker(): void
    {
        $schedule = ScheduleCleaning::where('status', ScheduleCleaningStatusEnum::Booked())
            ->first();
        $scheduleEmployee = $schedule->scheduleEmployees()->first();
        $schedule->update([
            'start_at' => now()->addYear(),
            'end_at' => now()->addYear()->addHour(),
        ]);
        $endTime = calculate_end_time(
            $schedule->start_at,
            calculate_calendar_quarters(
                $schedule->quarters,
                $schedule->scheduleEmployees->count() - 1
            ),
            format: 'Y-m-d H:i:s'
        );

        $this->actingAs($this->admin)
            ->deleteJson("/schedules/{$schedule->id}/workers/{$scheduleEmployee->user_id}/disable")
            ->assertStatus(Response::HTTP_OK)
            ->assertJson(fn (AssertableJson $json) => $json
                ->whereAllType([
                    'message' => 'string',
                    'data' => 'array',
                ])
                ->where('message', __('worker disabled successfully')));

        $this->assertSoftDeleted('schedule_employees', [
            'id' => $scheduleEmployee->id,
        ]);

        $this->assertDatabaseHas('schedule_cleanings', [
            'id' => $schedule->id,
            'end_at' => $endTime,
        ]);

        Bus::assertDispatchedAfterResponse(SendNotificationJob::class);
    }

    public function testCanNotDisableIfScheduleNotBookedAndNotProgress(): void
    {
        $schedule = ScheduleCleaning::whereNotIn(
            'status',
            [ScheduleCleaningStatusEnum::Booked(), ScheduleCleaningStatusEnum::Progress()]
        )->first();

        $this->actingAs($this->admin)
            ->deleteJson("/schedules/{$schedule->id}/workers/{$this->worker->id}/disable")
            ->assertStatus(Response::HTTP_BAD_REQUEST)
            ->assertJson(fn (AssertableJson $json) => $json
                ->whereAllType([
                    'error' => 'array',
                ])
                ->where('error.message', __('failed to disable worker due to schedule status')));
    }

    public function testCanNotDisableIfNotWorker(): void
    {
        $schedule = ScheduleCleaning::where('status', ScheduleCleaningStatusEnum::Booked())
            ->first();

        $this->actingAs($this->admin)
            ->deleteJson("/schedules/{$schedule->id}/workers/{$this->user->id}/disable")
            ->assertStatus(Response::HTTP_BAD_REQUEST)
            ->assertJson(fn (AssertableJson $json) => $json
                ->whereAllType([
                    'error' => 'array',
                ])
                ->where('error.message', __('user not worker')));
    }

    public function testCanNotDisableIfConflict(): void
    {
        $schedule = ScheduleCleaning::where('status', ScheduleCleaningStatusEnum::Booked())
            ->first();
        $scheduleEmployee = $schedule->scheduleEmployees()->first();

        $schedule2 = ScheduleCleaning::where('status', ScheduleCleaningStatusEnum::Booked())
            ->where('id', '!=', $schedule->id)
            ->first();
        $startAt = $schedule->end_at->copy()->subMinutes(1);
        $schedule2->update(['start_at' => $startAt]);

        $this->actingAs($this->admin)
            ->deleteJson("/schedules/{$schedule->id}/workers/{$scheduleEmployee->user_id}/disable")
            ->assertStatus(Response::HTTP_CONFLICT)
            ->assertJson(fn (AssertableJson $json) => $json
                ->whereAllType([
                    'error' => 'array',
                    'error.errors' => 'array',
                ])
                ->where('error.message', __('this action causes conflict with other schedules')));
    }

    public function testCanNotDisableIfWorkerNotInSchedule(): void
    {
        $schedule = ScheduleCleaning::where('status', ScheduleCleaningStatusEnum::Booked())
            ->first();

        $this->actingAs($this->admin)
            ->deleteJson("/schedules/{$schedule->id}/workers/{$this->worker->id}/disable")
            ->assertStatus(Response::HTTP_BAD_REQUEST)
            ->assertJson(fn (AssertableJson $json) => $json
                ->whereAllType([
                    'error' => 'array',
                ])
                ->where('error.message', __('worker not in schedule')));
    }

    public function testCanRemoveWorker(): void
    {
        $schedule = ScheduleCleaning::where('status', ScheduleCleaningStatusEnum::Booked())
            ->first();
        $scheduleEmployee = $schedule->scheduleEmployees()->first();
        $schedule->update([
            'start_at' => now()->addYear(),
            'end_at' => now()->addYear()->addHour(),
        ]);
        $endTime = calculate_end_time(
            $schedule->start_at,
            calculate_calendar_quarters(
                $schedule->quarters,
                $schedule->scheduleEmployees->count() - 1
            ),
            format: 'Y-m-d H:i:s'
        );
        $this->actingAs($this->admin)
            ->deleteJson("/schedules/{$schedule->id}/workers/{$scheduleEmployee->user_id}")
            ->assertStatus(Response::HTTP_OK)
            ->assertJson(fn (AssertableJson $json) => $json
                ->whereAllType([
                    'message' => 'string',
                    'data' => 'array',
                ])
                ->where('message', __('worker removed successfully')));

        $this->assertDatabaseMissing('schedule_employees', [
            'id' => $scheduleEmployee->id,
        ]);

        $this->assertDatabaseHas('schedule_cleanings', [
            'id' => $schedule->id,
            'end_at' => $endTime,
        ]);

        Bus::assertDispatchedAfterResponse(SendNotificationJob::class);
    }

    public function testCanNotRemoveIfScheduleNotBookedAndNotProgress(): void
    {
        $schedule = ScheduleCleaning::whereNotIn(
            'status',
            [ScheduleCleaningStatusEnum::Booked(), ScheduleCleaningStatusEnum::Progress()]
        )->first();

        $this->actingAs($this->admin)
            ->deleteJson("/schedules/{$schedule->id}/workers/{$this->worker->id}")
            ->assertStatus(Response::HTTP_BAD_REQUEST)
            ->assertJson(fn (AssertableJson $json) => $json
                ->whereAllType([
                    'error' => 'array',
                ])
                ->where('error.message', __('failed to remove worker due to schedule status')));
    }

    public function testCanNotRemoveIfNotWorker(): void
    {
        $schedule = ScheduleCleaning::where('status', ScheduleCleaningStatusEnum::Booked())
            ->first();

        $this->actingAs($this->admin)
            ->deleteJson("/schedules/{$schedule->id}/workers/{$this->user->id}")
            ->assertStatus(Response::HTTP_BAD_REQUEST)
            ->assertJson(fn (AssertableJson $json) => $json
                ->whereAllType([
                    'error' => 'array',
                ])
                ->where('error.message', __('user not worker')));
    }

    public function testCanNotRemoveIfConflict(): void
    {
        $schedule = ScheduleCleaning::where('status', ScheduleCleaningStatusEnum::Booked())
            ->first();
        $scheduleEmployee = $schedule->scheduleEmployees()->first();

        $schedule2 = ScheduleCleaning::where('status', ScheduleCleaningStatusEnum::Booked())
            ->where('id', '!=', $schedule->id)
            ->first();
        $startAt = $schedule->end_at->copy()->subMinutes(1);
        $schedule2->update(['start_at' => $startAt]);

        $this->actingAs($this->admin)
            ->deleteJson("/schedules/{$schedule->id}/workers/{$scheduleEmployee->user_id}")
            ->assertStatus(Response::HTTP_CONFLICT)
            ->assertJson(fn (AssertableJson $json) => $json
                ->whereAllType([
                    'error' => 'array',
                    'error.errors' => 'array',
                ])
                ->where('error.message', __('this action causes conflict with other schedules')));
    }

    public function testCanNotRemoveIfWorkerNotInSchedule(): void
    {
        $schedule = ScheduleCleaning::where('status', ScheduleCleaningStatusEnum::Booked())
            ->first();

        $this->actingAs($this->admin)
            ->deleteJson("/schedules/{$schedule->id}/workers/{$this->worker->id}")
            ->assertStatus(Response::HTTP_BAD_REQUEST)
            ->assertJson(fn (AssertableJson $json) => $json
                ->whereAllType([
                    'error' => 'array',
                ])
                ->where('error.message', __('worker not in schedule')));
    }

    public function testCanRevertWorker(): void
    {
        $schedule = ScheduleCleaning::where('status', ScheduleCleaningStatusEnum::Booked())
            ->first();
        $scheduleEmployee = $schedule->scheduleEmployees()->first();
        $scheduleEmployee->update(['status' => ScheduleCleaningStatusEnum::Cancel()]);

        $this->actingAs($this->admin)
            ->postJson("/schedules/{$schedule->id}/workers/{$scheduleEmployee->user_id}/revert")
            ->assertStatus(Response::HTTP_OK)
            ->assertJson(fn (AssertableJson $json) => $json
                ->whereAllType([
                    'message' => 'string',
                    'data' => 'array',
                ])
                ->where('message', __('worker reverted successfully')));

        $this->assertDatabaseHas('schedule_employees', [
            'id' => $scheduleEmployee->id,
            'status' => ScheduleCleaningStatusEnum::Pending(),
            'deleted_at' => null,
        ]);

        $this->assertDatabaseMissing('deviations', [
            'schedule_cleaning_id' => $scheduleEmployee->scheduleable_id,
            'user_id' => $scheduleEmployee->user_id,
            'type' => DeviationTypeEnum::Canceled(),
        ]);

        Bus::assertDispatchedAfterResponse(SendNotificationJob::class);
    }

    public function testCanRevertWorkerInCanceledSchedule(): void
    {
        $creditService = new CreditService();
        $orderService = new OrderService();
        $schedule = ScheduleCleaning::where('status', ScheduleCleaningStatusEnum::Cancel())
            ->first();
        $scheduleEmployee = $schedule->scheduleEmployees()->first();
        $scheduleEmployee->update(['status' => ScheduleCleaningStatusEnum::Cancel()]);
        $deviation = $schedule->deviation()->create([
            'types' => [DeviationTypeEnum::Canceled()],
            'reason' => 'test',
            'causer_id' => $this->admin->id,
        ]);
        $deviation->delete();

        $changeRequest = $schedule->changeRequest()->create([
            'status' => ScheduleCleaningChangeStatusEnum::Canceled(),
            'causer_id' => $this->admin->id,
            'original_start_at' => now(),
            'original_end_at' => now()->addHour(),
        ]);

        $creditService->refund(
            $schedule,
            null,
            null,
            $this->admin->id,
        );

        Invoice::whereNotNull('id')->forceDelete();
        Order::whereNotNull('id')->forceDelete();
        ScheduleCleaning::whereNot('id', $schedule->id)->forceDelete();
        [$order] = $orderService->createOrder($schedule, true);
        $orderService->cancelByCustomer($order, $schedule);

        $creditTransaction = CreditTransaction::where('schedule_cleaning_id', $schedule->id)
            ->where('user_id', $schedule->subscription->user_id)
            ->where('type', CreditTransactionTypeEnum::Refund())
            ->latest()
            ->first();

        $this->actingAs($this->admin)
            ->postJson("/schedules/{$schedule->id}/workers/{$scheduleEmployee->user_id}/revert")
            ->assertStatus(Response::HTTP_OK)
            ->assertJson(fn (AssertableJson $json) => $json
                ->whereAllType([
                    'message' => 'string',
                    'data' => 'array',
                ])
                ->where('message', __('worker reverted successfully')));

        $this->assertDatabaseHas('schedule_employees', [
            'id' => $scheduleEmployee->id,
            'status' => ScheduleCleaningStatusEnum::Pending(),
            'deleted_at' => null,
        ]);

        $this->assertDatabaseMissing('deviations', [
            'schedule_cleaning_id' => $scheduleEmployee->scheduleable_id,
            'user_id' => $scheduleEmployee->user_id,
            'type' => DeviationTypeEnum::Canceled(),
        ]);

        $this->assertDatabaseHas('schedule_cleaning_deviations', [
            'id' => $deviation->id,
        ]);

        $this->assertDatabaseHas('schedule_cleaning_change_requests', [
            'id' => $changeRequest->id,
            'status' => ScheduleCleaningChangeStatusEnum::Pending(),
            'causer_id' => null,
            'original_start_at' => null,
            'original_end_at' => null,
        ]);

        if ($creditTransaction) {
            $this->assertDatabaseHas('credit_transactions', [
                'user_id' => $schedule->subscription->user_id,
                'schedule_cleaning_id' => $schedule->id,
                'issuer_id' => $this->admin->id,
                'type' => CreditTransactionTypeEnum::Updated(),
                'total_amount' => $creditTransaction->total_amount,
                'description' => __('update credit due to schedule reverted by admin'),
            ]);
        }

        $this->assertDatabaseMissing('orders', [
            'id' => $order->id,
        ]);

        $this->assertDatabaseMissing('invoices', [
            'id' => $order->invoice_id,
        ]);

        $this->assertDatabaseMissing('order_rows', [
            'order_id' => $order->id,
        ]);

        Bus::assertDispatchedAfterResponse(SendNotificationJob::class);
    }

    public function testCanNotRevertIfScheduleNotBookedAndNotProgress(): void
    {
        $schedule = ScheduleCleaning::whereNotIn(
            'status',
            [
                ScheduleCleaningStatusEnum::Booked(),
                ScheduleCleaningStatusEnum::Progress(),
                ScheduleCleaningStatusEnum::Cancel(),
            ]
        )->first();

        $this->actingAs($this->admin)
            ->postJson("/schedules/{$schedule->id}/workers/{$this->worker->id}/revert")
            ->assertStatus(Response::HTTP_BAD_REQUEST)
            ->assertJson(fn (AssertableJson $json) => $json
                ->whereAllType([
                    'error' => 'array',
                ])
                ->where('error.message', __('failed to revert worker due to schedule status')));
    }

    public function testCanNotRevertIfNotWorker(): void
    {
        $schedule = ScheduleCleaning::where('status', ScheduleCleaningStatusEnum::Booked())
            ->first();

        $this->actingAs($this->admin)
            ->postJson("/schedules/{$schedule->id}/workers/{$this->user->id}/revert")
            ->assertStatus(Response::HTTP_BAD_REQUEST)
            ->assertJson(fn (AssertableJson $json) => $json
                ->whereAllType([
                    'error' => 'array',
                ])
                ->where('error.message', __('user not worker')));
    }

    public function testCanNotRevertIfWorkerNotInSchedule(): void
    {
        $schedule = ScheduleCleaning::where('status', ScheduleCleaningStatusEnum::Booked())
            ->first();

        $this->actingAs($this->admin)
            ->postJson("/schedules/{$schedule->id}/workers/{$this->worker->id}/revert")
            ->assertStatus(Response::HTTP_BAD_REQUEST)
            ->assertJson(fn (AssertableJson $json) => $json
                ->whereAllType([
                    'error' => 'array',
                ])
                ->where('error.message', __('worker not in schedule')));
    }

    public function testCanNotRevertWorkerDueToBlockDay(): void
    {
        $schedule = ScheduleCleaning::where('status', ScheduleCleaningStatusEnum::Cancel())
            ->first();
        $scheduleEmployee = $schedule->scheduleEmployees()->first();
        $scheduleEmployee->update(['status' => ScheduleCleaningStatusEnum::Cancel()]);
        $time = Carbon::parse($schedule->start_at);

        BlockDay::create([
            'block_date' => $time->format('Y-m-d'),
            'start_block_time' => $time->startOfDay()->format('H:i:s'),
            'end_block_time' => $time->endOfDay()->format('H:i:s'),
        ]);

        $this->actingAs($this->admin)
            ->postJson("/schedules/{$schedule->id}/workers/{$scheduleEmployee->user_id}/revert")
            ->assertStatus(Response::HTTP_BAD_REQUEST)
            ->assertJson(fn (AssertableJson $json) => $json
                ->whereAllType([
                    'error' => 'array',
                ])
                ->where('error.message', __('failed to revert worker due to block day')));
    }

    public function testCanNotRevertWorkerDueToScheduleCollision(): void
    {
        $schedule1 = ScheduleCleaning::where('status', ScheduleCleaningStatusEnum::Cancel())
            ->first();
        $scheduleEmployee = $schedule1->scheduleEmployees()->first();
        $scheduleEmployee->update(['status' => ScheduleCleaningStatusEnum::Cancel()]);

        $schedule2 = ScheduleCleaning::where('status', ScheduleCleaningStatusEnum::Booked())->first();
        $schedule2->update(['start_at' => $schedule1->start_at]);

        $this->actingAs($this->admin)
            ->postJson("/schedules/{$schedule1->id}/workers/{$scheduleEmployee->user_id}/revert")
            ->assertStatus(Response::HTTP_BAD_REQUEST)
            ->assertJson(fn (AssertableJson $json) => $json
                ->whereAllType([
                    'error' => 'array',
                ])
                ->where('error.message', __('failed to revert worker due to schedule collision')));
    }

    public function testCanNotRevertWorkerDueToInsufficientCredit(): void
    {
        $creditService = new CreditService();
        $schedule = ScheduleCleaning::where('status', ScheduleCleaningStatusEnum::Cancel())
            ->first();
        $scheduleEmployee = $schedule->scheduleEmployees()->first();
        $scheduleEmployee->update(['status' => ScheduleCleaningStatusEnum::Cancel()]);
        ScheduleCleaning::whereNot('id', $schedule->id)->forceDelete();

        $schedule->products()->create([
            'product_id' => 1,
            'quantity' => 1,
            'price' => 100,
            'discount_percentage' => 100,
            'payment_method' => CleaningProductPaymentMethodEnum::Credit(),
        ]);

        $total = $creditService->getTotal($schedule->subscription->user_id);
        $creditService->createTransaction(
            $schedule->subscription->user_id,
            CreditTransactionTypeEnum::Updated(),
            $total,
            __('refund for schedule canceled'),
            $schedule->id,
            $this->admin->id,
        );

        $transactionAmount = $creditService->calculateRefund($schedule);
        $creditTransaction = CreditTransaction::where('schedule_cleaning_id', $schedule->id)
            ->where('user_id', $schedule->subscription->user_id)
            ->where('type', CreditTransactionTypeEnum::Refund())
            ->where('total_amount', $transactionAmount)
            ->latest()
            ->first();

        /** @var \Illuminate\Database\Eloquent\Collection<array-key,CleaningProduct> $products */
        $products = $schedule->products()
            ->where('payment_method', CleaningProductPaymentMethodEnum::Credit())
            ->get();

        $totalCredit = $creditTransaction ? $creditTransaction->total_amount : 0;
        $productsCreditSum = $products->sum(function ($product) {
            return $product->product->credit_price;
        });
        $totalCredit += $productsCreditSum;

        $this->actingAs($this->admin)
            ->postJson("/schedules/{$schedule->id}/workers/{$scheduleEmployee->user_id}/revert")
            ->assertStatus(Response::HTTP_BAD_REQUEST)
            ->assertJson(fn (AssertableJson $json) => $json
                ->whereAllType([
                    'error' => 'array',
                ])
                ->where(
                    'error.message',
                    __('failed to revert worker due to insufficient credit', ['total_credit' => $totalCredit])
                ));
    }

    public function testCanFindAvailableWorkers(): void
    {
        $data = [
            'workerIds' => [$this->worker->id],
            'startAt' => now()->addYear()->addHour()->format('Y-m-d H:i:s'),
            'endAt' => now()->addYear()->addHours(2)->format('Y-m-d H:i:s'),
        ];
        $query = http_build_query($data);
        $response = $this->actingAs($this->admin)
            ->get("/schedules/workers/available?$query");
        $keys = array_keys(
            UserResponseDTO::from(User::first())->toArray()
        );

        $response->assertStatus(200);

        $response->assertJsonStructure([
            'data' => [
                '*' => $keys,
            ],
        ]);
    }

    public function testCanUpdateAttendanceWithoutTimeAdjustment(): void
    {
        $schedule = ScheduleCleaning::first();
        $scheduleEmployee = $schedule->scheduleEmployees()->first();
        $data = [
            'startAt' => now()->addHour()->format('Y-m-d H:i:s'),
            'endAt' => now()->addHours(2)->format('Y-m-d H:i:s'),
        ];

        $this->actingAs($this->admin)
            ->patch("/schedules/{$schedule->id}/workers/{$scheduleEmployee->user_id}/attendance", $data)
            ->assertStatus(302)
            ->assertRedirect()
            ->assertSessionHas('success', __('worker attendance updated successfully'));

        $this->assertDatabaseHas('schedule_employees', [
            'id' => $scheduleEmployee->id,
            'start_at' => $data['startAt'],
            'end_at' => $data['endAt'],
        ]);

        $this->assertDatabaseMissing('time_adjustments', [
            'schedule_employee_id' => $scheduleEmployee->id,
        ]);
    }

    public function testCanUpdateAttendanceWithTimeAdjustment(): void
    {
        $schedule = ScheduleCleaning::first();
        $scheduleEmployee = $schedule->scheduleEmployees()->first();
        $workTime = $schedule->scheduleEmployees->sum('total_work_time');
        $quarters = ceil($workTime / (60 * 15));
        $quarters = $quarters > $schedule->quarters ? $schedule->quarters : $quarters;

        $data = [
            'startAt' => now()->addHour()->format('Y-m-d H:i:s'),
            'endAt' => now()->addHours(2)->format('Y-m-d H:i:s'),
            'timeAdjustment' => [
                'quarters' => $quarters,
                'reason' => 'test',
            ],
        ];

        $this->actingAs($this->admin)
            ->patch("/schedules/{$schedule->id}/workers/{$scheduleEmployee->user_id}/attendance", $data)
            ->assertStatus(302)
            ->assertRedirect()
            ->assertSessionHas('success', __('worker attendance updated successfully'));

        $this->assertDatabaseHas('schedule_employees', [
            'id' => $scheduleEmployee->id,
            'start_at' => $data['startAt'],
            'end_at' => $data['endAt'],
        ]);

        $this->assertDatabaseHas('time_adjustments', [
            'schedule_employee_id' => $scheduleEmployee->id,
            'causer_id' => $this->admin->id,
            'quarters' => $data['timeAdjustment']['quarters'],
            'reason' => $data['timeAdjustment']['reason'],
        ]);
    }

    public function testCanNotUpdateAttendanceIfWorkerNotInSchedule(): void
    {
        $schedule = ScheduleCleaning::first();
        $scheduleEmployee = $schedule->scheduleEmployees()->first();

        $data = [
            'startAt' => now()->addHour()->format('Y-m-d H:i:s'),
            'endAt' => now()->addHours(2)->format('Y-m-d H:i:s'),
        ];

        $this->actingAs($this->admin)
            ->patch("/schedules/{$schedule->id}/workers/{$this->worker->id}/attendance", $data)
            ->assertStatus(302)
            ->assertRedirect()
            ->assertSessionHas('error', __('worker not in schedule'));

        $this->assertDatabaseHas('schedule_employees', [
            'id' => $scheduleEmployee->id,
            'start_at' => $scheduleEmployee->start_at,
            'end_at' => $scheduleEmployee->end_at,
        ]);

        $this->assertDatabaseMissing('time_adjustments', [
            'schedule_employee_id' => $scheduleEmployee->id,
        ]);
    }

    public function testCanNotUpdateAttendanceIfQuartersLessThanZero(): void
    {
        $schedule = ScheduleCleaning::first();
        $scheduleEmployee = $schedule->scheduleEmployees()->first();
        $startAt = now()->addHour();
        $endAt = now()->addHours(2);
        $workQuarters = $startAt->diffInMinutes($endAt) / 15;
        $data = [
            'startAt' => $startAt->format('Y-m-d H:i:s'),
            'endAt' => $endAt->format('Y-m-d H:i:s'),
            'timeAdjustment' => [
                'quarters' => -($workQuarters + 1),
                'reason' => 'test',
            ],
        ];

        $this->actingAs($this->admin)
            ->patch("/schedules/{$schedule->id}/workers/{$scheduleEmployee->user_id}/attendance", $data)
            ->assertStatus(302)
            ->assertRedirect()
            ->assertSessionHas('error', __('total quarters employee worked on cannot be less than 0'));

        $this->assertDatabaseHas('schedule_employees', [
            'id' => $scheduleEmployee->id,
            'start_at' => $scheduleEmployee->start_at,
            'end_at' => $scheduleEmployee->end_at,
        ]);

        $this->assertDatabaseMissing('time_adjustments', [
            'schedule_employee_id' => $scheduleEmployee->id,
        ]);
    }
}
