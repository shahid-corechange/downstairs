<?php

namespace Tests\Api;

use App\Enums\Auth\TokenAbilityEnum;
use App\Enums\ScheduleCleaning\CleaningProductPaymentMethodEnum;
use App\Enums\ScheduleCleaning\ScheduleCleaningStatusEnum;
use App\Enums\ScheduleEmployee\ScheduleEmployeeStatusEnum;
use App\Jobs\SendNotificationJob;
use App\Models\ScheduleCleaning;
use App\Models\Subscription;
use Bus;
use Carbon\Carbon;
use Illuminate\Http\Response;
use Illuminate\Testing\Fluent\AssertableJson;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ApiWorkerTest extends TestCase
{
    public function testAuthenticatedWorkerCanGetScheduleEmployeesWithApi(): void
    {
        Sanctum::actingAs($this->worker, [TokenAbilityEnum::APIAccess()]);
        $response = $this->getJson('/api/v0/schedule-employees');

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson(fn (AssertableJson $json) => $json
                ->whereAllType([
                    'apiVersion' => 'string',
                    'data' => 'array',
                    'pagination' => 'array',
                    'meta' => 'array',
                ]));
    }

    public function testAuthenticatedWorkerCanGetScheduleEmployeesWithApiWithQuery(): void
    {
        Sanctum::actingAs($this->worker, [TokenAbilityEnum::APIAccess()]);
        $response = $this->getJson('/api/v0/schedule-employees?page=1&size=2&sort=orderId.asc');

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson(fn (AssertableJson $json) => $json
                ->whereAllType([
                    'apiVersion' => 'string',
                    'data' => 'array',
                    'pagination' => 'array',
                    'meta' => 'array',
                ]));
    }

    public function testAuthenticatedWorkerCanNotGetScheduleEmployeesWithApiWithQuery(): void
    {
        Sanctum::actingAs($this->worker, [TokenAbilityEnum::APIAccess()]);
        $response = $this->getJson('/api/v0/schedule-employees?page=1&size=2&order.between=1');

        $response->assertStatus(Response::HTTP_BAD_REQUEST)
            ->assertJson(fn (AssertableJson $json) => $json
                ->whereAllType([
                    'apiVersion' => 'string',
                    'error' => 'array',
                ]));
    }

    public function testAuthenticatedUserCanNotGetScheduleEmployeesWithApi(): void
    {
        Sanctum::actingAs($this->user, [TokenAbilityEnum::APIAccess()]);
        $response = $this->getJson('/api/v0/schedule-employees');

        $response->assertStatus(Response::HTTP_FORBIDDEN)
            ->assertJson(fn (AssertableJson $json) => $json
                ->whereAllType([
                    'apiVersion' => 'string',
                    'error' => 'array',
                ]));
    }

    public function testAuthenticatedWorkerCanStartPendingScheduleWithApi(): void
    {
        Sanctum::actingAs($this->worker, [TokenAbilityEnum::APIAccess()]);
        $faker = fake();
        $subscription = Subscription::first();
        $scheduleCleaning = ScheduleCleaning::factory()
            ->forSubscription($subscription)
            ->forStatus(ScheduleCleaningStatusEnum::Pending())
            ->create();
        $scheduleCleaning->fill([
            'start_at' => Carbon::now(),
            'end_at' => Carbon::now()->addHour(),
        ])->save();
        $scheduleCleaning->refresh();
        $scheduleEmployee = $scheduleCleaning->scheduleEmployees()->create([
            'user_id' => $this->worker->id,
            'status' => ScheduleEmployeeStatusEnum::Pending(),
        ]);

        $response = $this->postJson(
            "/api/v0/schedule-employees/{$scheduleEmployee->id}/start",
            [
                'startLatitude' => $faker->latitude,
                'startLongitude' => $faker->longitude,
                'startIp' => $faker->ipv4,
            ]
        );

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson(fn (AssertableJson $json) => $json
                ->whereAllType([
                    'apiVersion' => 'string',
                    'data' => 'array',
                ]));

        Bus::assertDispatchedAfterResponse(SendNotificationJob::class);
    }

    public function testAuthenticatedWorkerCanNotStartPastScheduleWithApi(): void
    {
        Sanctum::actingAs($this->worker, [TokenAbilityEnum::APIAccess()]);
        $faker = fake();
        $subscription = Subscription::first();
        $scheduleCleaning = ScheduleCleaning::factory()
            ->forSubscription($subscription)
            ->forStatus(ScheduleCleaningStatusEnum::Pending())
            ->create();
        $date = Carbon::now()->subDay();
        $scheduleCleaning->fill([
            'start_at' => $date,
            'end_at' => $date->copy()->addHour(),
        ])->save();
        $scheduleCleaning->refresh();
        $scheduleEmployee = $scheduleCleaning->scheduleEmployees()->create([
            'user_id' => $this->worker->id,
            'status' => ScheduleEmployeeStatusEnum::Pending(),
        ]);

        $response = $this->postJson(
            "/api/v0/schedule-employees/{$scheduleEmployee->id}/start",
            [
                'startLatitude' => $faker->latitude,
                'startLongitude' => $faker->longitude,
                'startIp' => $faker->ipv4,
            ]
        );

        $response->assertStatus(Response::HTTP_BAD_REQUEST)
            ->assertJson(fn (AssertableJson $json) => $json
                ->whereAllType([
                    'apiVersion' => 'string',
                    'error' => 'array',
                ]))
            ->assertJsonPath('error.message', __('can not start past schedule'));
    }

    public function testAuthenticatedWorkerCanNotStartDoneScheduleWithApi()
    {
        Sanctum::actingAs($this->worker, [TokenAbilityEnum::APIAccess()]);
        $faker = fake();
        $subscription = Subscription::first();
        $scheduleCleaning = ScheduleCleaning::factory()
            ->forSubscription($subscription)
            ->forStatus(ScheduleCleaningStatusEnum::Done())
            ->create();
        $scheduleCleaning->fill([
            'start_at' => Carbon::now(),
            'end_at' => Carbon::now()->addHour(),
        ])->save();
        $scheduleCleaning->refresh();
        $scheduleEmployee = $scheduleCleaning->scheduleEmployees()->create([
            'user_id' => $this->worker->id,
            'status' => ScheduleEmployeeStatusEnum::Done(),
        ]);

        $response = $this->postJson(
            "/api/v0/schedule-employees/{$scheduleEmployee->id}/start",
            [
                'startLatitude' => $faker->latitude,
                'startLongitude' => $faker->longitude,
                'startIp' => $faker->ipv4,
            ]
        );

        $response->assertStatus(Response::HTTP_BAD_REQUEST)
            ->assertJson(fn (AssertableJson $json) => $json
                ->whereAllType([
                    'apiVersion' => 'string',
                    'error' => 'array',
                ]))
            ->assertJsonPath('error.message', __('schedule cleaning already done'));
    }

    public function testAuthenticatedWorkerCanNotStartDifferentDayScheduleWithApi(): void
    {
        Sanctum::actingAs($this->worker, [TokenAbilityEnum::APIAccess()]);
        $faker = fake();
        $subscription = Subscription::first();
        $scheduleCleaning = ScheduleCleaning::factory()
            ->forSubscription($subscription)
            ->forStatus(ScheduleCleaningStatusEnum::Pending())
            ->create();
        $date = Carbon::now()->addDay();
        $scheduleCleaning->update([
            'start_at' => $date,
            'end_at' => $date->copy()->addHour(),
        ]);
        $scheduleEmployee = $scheduleCleaning->scheduleEmployees()->create([
            'user_id' => $this->worker->id,
            'status' => ScheduleEmployeeStatusEnum::Pending(),
        ]);

        $response = $this->postJson(
            "/api/v0/schedule-employees/{$scheduleEmployee->id}/start",
            [
                'startLatitude' => $faker->latitude,
                'startLongitude' => $faker->longitude,
                'startIp' => $faker->ipv4,
            ]
        );

        $response->assertStatus(Response::HTTP_BAD_REQUEST)
            ->assertJson(fn (AssertableJson $json) => $json
                ->whereAllType([
                    'apiVersion' => 'string',
                    'error' => 'array',
                ]))
            ->assertJsonPath('error.message', __('can not start schedule in different day'));
    }

    public function testAuthenticatedWorkerCanNotStartTooSoonScheduleWithApi(): void
    {
        Sanctum::actingAs($this->worker, [TokenAbilityEnum::APIAccess()]);
        $faker = fake();
        $subscription = Subscription::first();
        $scheduleCleaning = ScheduleCleaning::factory()
            ->forSubscription($subscription)
            ->forStatus(ScheduleCleaningStatusEnum::Pending())
            ->create();
        $date = Carbon::now()->addMinutes(config('downstairs.schedule.employee.minStartMinutes') + 10);
        $scheduleCleaning->update([
            'start_at' => $date,
            'end_at' => $date->copy()->addHour(),
        ]);
        $scheduleEmployee = $scheduleCleaning->scheduleEmployees()->create([
            'user_id' => $this->worker->id,
            'status' => ScheduleEmployeeStatusEnum::Pending(),
        ]);

        $response = $this->postJson(
            "/api/v0/schedule-employees/{$scheduleEmployee->id}/start",
            [
                'startLatitude' => $faker->latitude,
                'startLongitude' => $faker->longitude,
                'startIp' => $faker->ipv4,
            ]
        );

        $response->assertStatus(Response::HTTP_BAD_REQUEST)
            ->assertJson(fn (AssertableJson $json) => $json
                ->whereAllType([
                    'apiVersion' => 'string',
                    'error' => 'array',
                ]))
            ->assertJsonPath('error.message', __('too soon to start'));
    }

    public function testAuthenticatedWorkerCanNotStartInProgressScheduleWithApi(): void
    {
        Sanctum::actingAs($this->worker, [TokenAbilityEnum::APIAccess()]);
        $faker = fake();
        $subscription = Subscription::first();
        $scheduleCleaning = ScheduleCleaning::factory()
            ->forSubscription($subscription)
            ->forStatus(ScheduleCleaningStatusEnum::Pending())
            ->create();
        $date = Carbon::now();
        $scheduleCleaning->fill([
            'start_at' => $date,
            'end_at' => $date->copy()->addHour(),
        ])->save();
        $scheduleCleaning->refresh();
        $scheduleEmployee = $scheduleCleaning->scheduleEmployees()->create([
            'user_id' => $this->worker->id,
            'status' => ScheduleEmployeeStatusEnum::Pending(),
        ]);
        $scheduleCleaning->scheduleEmployees()->create([
            'user_id' => $this->worker->id,
            'status' => ScheduleEmployeeStatusEnum::Progress(),
        ]);

        $response = $this->postJson(
            "/api/v0/schedule-employees/{$scheduleEmployee->id}/start",
            [
                'startLatitude' => $faker->latitude,
                'startLongitude' => $faker->longitude,
                'startIp' => $faker->ipv4,
            ]
        );

        $response->assertStatus(Response::HTTP_BAD_REQUEST)
            ->assertJson(fn (AssertableJson $json) => $json
                ->whereAllType([
                    'apiVersion' => 'string',
                    'error' => 'array',
                ]))
            ->assertJsonPath('error.message', __('you have in progress schedule'));
    }

    public function testWorkerDifferentTeamCanNotStartScheduleWithApi(): void
    {
        Sanctum::actingAs($this->worker, [TokenAbilityEnum::APIAccess()]);
        $faker = fake();
        $response = $this->postJson('/api/v0/schedule-employees/1/start', [
            'startLatitude' => $faker->latitude,
            'startLongitude' => $faker->longitude,
            'startIp' => $faker->ipv4,
        ]);

        $response->assertStatus(Response::HTTP_NOT_FOUND)
            ->assertJson(fn (AssertableJson $json) => $json
                ->whereAllType([
                    'apiVersion' => 'string',
                    'error' => 'array',
                ]));
    }

    public function testCanNotStartDoneOrderWithApi(): void
    {
        Sanctum::actingAs($this->worker, [TokenAbilityEnum::APIAccess()]);
        $faker = fake();
        $subscription = Subscription::first();
        $scheduleCleaning = ScheduleCleaning::factory()
            ->forSubscription($subscription)
            ->forStatus(ScheduleCleaningStatusEnum::Done())
            ->create();
        $scheduleCleaning->refresh();
        $scheduleEmployee = $scheduleCleaning->scheduleEmployees()->create([
            'user_id' => $this->worker->id,
            'status' => ScheduleEmployeeStatusEnum::Done(),
        ]);

        $response = $this->postJson(
            "/api/v0/schedule-employees/{$scheduleEmployee->id}/start",
            [
                'startLatitude' => $faker->latitude,
                'startLongitude' => $faker->longitude,
                'startIp' => $faker->ipv4,
            ]
        );

        $response->assertStatus(Response::HTTP_BAD_REQUEST)
            ->assertJson(fn (AssertableJson $json) => $json
                ->whereAllType([
                    'apiVersion' => 'string',
                    'error' => 'array',
                ]));
    }

    public function testCanNotStartTooSoonScheduleWithApi(): void
    {
        Sanctum::actingAs($this->worker, [TokenAbilityEnum::APIAccess()]);
        $faker = fake();
        $subscription = Subscription::first();
        $scheduleCleaning = ScheduleCleaning::factory()
            ->forSubscription($subscription)
            ->forStatus(ScheduleCleaningStatusEnum::Pending())
            ->create();
        $scheduleCleaning->refresh();
        $scheduleEmployee = $scheduleCleaning->scheduleEmployees()->create([
            'user_id' => $this->worker->id,
            'status' => ScheduleEmployeeStatusEnum::Pending(),
        ]);

        $response = $this->postJson(
            "/api/v0/schedule-employees/{$scheduleEmployee->id}/start",
            [
                'startLatitude' => $faker->latitude,
                'startLongitude' => $faker->longitude,
                'startIp' => $faker->ipv4,
            ]
        );

        $response->assertStatus(Response::HTTP_BAD_REQUEST)
            ->assertJson(fn (AssertableJson $json) => $json
                ->whereAllType([
                    'apiVersion' => 'string',
                    'error' => 'array',
                ]));
    }

    public function testCanNotStartScheduleIfThereAreProgressScheduleWithApi(): void
    {
        Sanctum::actingAs($this->worker, [TokenAbilityEnum::APIAccess()]);
        $faker = fake();
        $subscription = Subscription::first();
        $scheduleCleaning = ScheduleCleaning::factory()
            ->forSubscription($subscription)
            ->forStatus(ScheduleCleaningStatusEnum::Pending())
            ->create();
        $scheduleCleaning->fill([
            'start_at' => Carbon::now(),
            'end_at' => Carbon::now()->addHour(),
        ])->save();
        $scheduleCleaning->refresh();
        $scheduleCleaning->scheduleEmployees()->create([
            'user_id' => $this->worker->id,
            'status' => ScheduleEmployeeStatusEnum::Progress(),
        ]);
        $scheduleEmployee = $scheduleCleaning->scheduleEmployees()->create([
            'user_id' => $this->worker->id,
            'status' => ScheduleEmployeeStatusEnum::Pending(),
        ]);

        $response = $this->postJson(
            "/api/v0/schedule-employees/{$scheduleEmployee->id}/start",
            [
                'startLatitude' => $faker->latitude,
                'startLongitude' => $faker->longitude,
                'startIp' => $faker->ipv4,
            ]
        );

        $response->assertStatus(Response::HTTP_BAD_REQUEST)
            ->assertJson(fn (AssertableJson $json) => $json
                ->whereAllType([
                    'apiVersion' => 'string',
                    'error' => 'array',
                ]));
    }

    public function testAuthenticatedUserCanNotStartScheduleWithApi(): void
    {
        Sanctum::actingAs($this->user, [TokenAbilityEnum::APIAccess()]);
        $response = $this->postJson('/api/v0/schedule-employees/1/start');

        $response->assertStatus(Response::HTTP_FORBIDDEN)
            ->assertJson(fn (AssertableJson $json) => $json
                ->whereAllType([
                    'apiVersion' => 'string',
                    'error' => 'array',
                ]));
    }

    public function testAuthenticatedWorkerCanEndScheduleWithApi(): void
    {
        Sanctum::actingAs($this->worker, [TokenAbilityEnum::APIAccess()]);
        $faker = fake();
        $subscription = Subscription::first();
        $scheduleCleaning = ScheduleCleaning::factory()
            ->forSubscription($subscription)
            ->forStatus(ScheduleCleaningStatusEnum::Progress())
            ->create();
        $scheduleCleaning->update([
            'start_at' => Carbon::now(),
            'end_at' => Carbon::now()->addHour(),
        ]);
        $scheduleEmployee = $scheduleCleaning->scheduleEmployees()->create([
            'start_latitude' => $faker->latitude,
            'start_longitude' => $faker->longitude,
            'start_ip' => $faker->ipv4,
            'start_at' => $scheduleCleaning->start_at,
            'user_id' => $this->worker->id,
            'status' => ScheduleEmployeeStatusEnum::Progress(),
        ]);

        $scheduleCleaning->scheduleCleaningTasks()->createMany([
            ['custom_task_id' => 1],
            ['custom_task_id' => 2],
            ['custom_task_id' => 3],
        ]);
        $taskIds = $scheduleCleaning->scheduleCleaningTasks->pluck('id')->toArray();

        $response = $this->postJson(
            "/api/v0/schedule-employees/{$scheduleEmployee->id}/end",
            [
                'endLatitude' => $faker->latitude,
                'endLongitude' => $faker->longitude,
                'endIp' => $faker->ipv4,
                'completedTaskIds' => $taskIds,
            ]
        );

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson(fn (AssertableJson $json) => $json
                ->whereAllType([
                    'apiVersion' => 'string',
                    'data' => 'array',
                ]));

        Bus::assertDispatchedAfterResponse(SendNotificationJob::class);
    }

    public function testCanNotEndNotOwnScheduleWithApi(): void
    {
        Sanctum::actingAs($this->worker, [TokenAbilityEnum::APIAccess()]);
        $faker = fake();
        $subscription = Subscription::first();
        $scheduleCleaning = ScheduleCleaning::factory()
            ->forSubscription($subscription)
            ->forStatus(ScheduleCleaningStatusEnum::Pending())
            ->create();
        $scheduleCleaning->scheduleEmployees()->create([
            'start_latitude' => $faker->latitude,
            'start_longitude' => $faker->longitude,
            'start_ip' => $faker->ipv4,
            'start_at' => $scheduleCleaning->start_at,
            'user_id' => 1,
            'status' => ScheduleEmployeeStatusEnum::Pending(),
        ]);

        $scheduleCleaning->scheduleCleaningTasks()->createMany([
            ['custom_task_id' => 1],
            ['custom_task_id' => 2],
            ['custom_task_id' => 3],
        ]);
        $taskIds = $scheduleCleaning->scheduleCleaningTasks->pluck('id')->toArray();

        $response = $this->postJson('/api/v0/schedule-employees/1/end', [
            'endLatitude' => $faker->latitude,
            'endLongitude' => $faker->longitude,
            'endIp' => $faker->ipv4,
            'completedTaskIds' => $taskIds,
        ]);

        $response->assertStatus(Response::HTTP_NOT_FOUND)
            ->assertJson(fn (AssertableJson $json) => $json
                ->whereAllType([
                    'apiVersion' => 'string',
                    'error' => 'array',
                ]));
    }

    public function testCanNotEndNotProgressScheduleWithApi(): void
    {
        Sanctum::actingAs($this->worker, [TokenAbilityEnum::APIAccess()]);
        $faker = fake();
        $subscription = Subscription::first();
        $scheduleCleaning = ScheduleCleaning::factory()
            ->forSubscription($subscription)
            ->forStatus(ScheduleCleaningStatusEnum::Pending())
            ->create();
        $scheduleEmployee = $scheduleCleaning->scheduleEmployees()->create([
            'start_latitude' => $faker->latitude,
            'start_longitude' => $faker->longitude,
            'start_ip' => $faker->ipv4,
            'start_at' => $scheduleCleaning->start_at,
            'user_id' => $this->worker->id,
            'status' => ScheduleEmployeeStatusEnum::Pending(),
        ]);

        $scheduleCleaning->scheduleCleaningTasks()->createMany([
            ['custom_task_id' => 1],
            ['custom_task_id' => 2],
            ['custom_task_id' => 3],
        ]);
        $taskIds = $scheduleCleaning->scheduleCleaningTasks->pluck('id')->toArray();

        $response = $this->postJson(
            "/api/v0/schedule-employees/{$scheduleEmployee->id}/end",
            [
                'endLatitude' => $faker->latitude,
                'endLongitude' => $faker->longitude,
                'endIp' => $faker->ipv4,
                'completedTaskIds' => $taskIds,
            ]
        );

        $response->assertStatus(Response::HTTP_BAD_REQUEST)
            ->assertJson(fn (AssertableJson $json) => $json
                ->whereAllType([
                    'apiVersion' => 'string',
                    'error' => 'array',
                ]));
    }

    public function testAuthenticatedUserCanNotEndScheduleWithApi(): void
    {
        Sanctum::actingAs($this->user, [TokenAbilityEnum::APIAccess()]);
        $response = $this->postJson('/api/v0/schedule-employees/1/end');

        $response->assertStatus(Response::HTTP_FORBIDDEN)
            ->assertJson(fn (AssertableJson $json) => $json
                ->whereAllType([
                    'apiVersion' => 'string',
                    'error' => 'array',
                ]));
    }

    public function testAuthenticatedWorkerCanCancelScheduleWithApi(): void
    {
        Sanctum::actingAs($this->worker, [TokenAbilityEnum::APIAccess()]);
        $subscription = Subscription::first();
        $scheduleCleaning = ScheduleCleaning::factory()
            ->forSubscription($subscription)
            ->forStatus(ScheduleCleaningStatusEnum::Pending())
            ->create();

        $scheduleCleaning->update([
            'start_at' => Carbon::now(),
            'end_at' => Carbon::now()->addHour(),
        ]);

        $scheduleCleaning->products()->create([
            'product_id' => 1,
            'quantity' => 1,
            'price' => 100,
            'discount_percentage' => 100,
            'payment_method' => CleaningProductPaymentMethodEnum::Credit(),
        ]);

        $scheduleEmployee = $scheduleCleaning->scheduleEmployees()->create([
            'user_id' => $this->worker->id,
            'status' => ScheduleEmployeeStatusEnum::Pending(),
        ]);

        $response = $this->postJson(
            "/api/v0/schedule-employees/{$scheduleEmployee->id}/cancel",
            ['description' => 'sick or unwell']
        );

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson(fn (AssertableJson $json) => $json
                ->whereAllType([
                    'apiVersion' => 'string',
                    'data' => 'array',
                ]));

        $this->assertDatabaseHas('schedule_cleanings', [
            'id' => $scheduleCleaning->id,
            'status' => ScheduleCleaningStatusEnum::Pending(),
        ]);

        $this->assertDatabaseHas('schedule_employees', [
            'scheduleable_type' => ScheduleCleaning::class,
            'scheduleable_id' => $scheduleCleaning->id,
            'status' => ScheduleEmployeeStatusEnum::Cancel(),
            'description' => 'sick or unwell',
        ]);
    }

    public function testDifferentWorkerCanNotCancelScheduleWithApi(): void
    {
        Sanctum::actingAs($this->worker, [TokenAbilityEnum::APIAccess()]);
        $response = $this->postJson(
            '/api/v0/schedule-employees/1/cancel',
            ['description' => 'sick or unwell']
        );

        $response->assertStatus(Response::HTTP_NOT_FOUND)
            ->assertJson(fn (AssertableJson $json) => $json
                ->whereAllType([
                    'apiVersion' => 'string',
                    'error' => 'array',
                ]));
    }

    public function testCanNotCancelDoneScheduleWithApi(): void
    {
        Sanctum::actingAs($this->worker, [TokenAbilityEnum::APIAccess()]);
        $subscription = Subscription::first();
        $scheduleCleaning = ScheduleCleaning::factory()
            ->forSubscription($subscription)
            ->forStatus(ScheduleCleaningStatusEnum::Done())
            ->create();

        $scheduleCleaning->update([
            'start_at' => Carbon::now(),
            'end_at' => Carbon::now()->addHour(),
        ]);

        $scheduleEmployee = $scheduleCleaning->scheduleEmployees()->create([
            'user_id' => $this->worker->id,
            'status' => ScheduleEmployeeStatusEnum::Pending(),
        ]);

        $response = $this->postJson(
            "/api/v0/schedule-employees/{$scheduleEmployee->id}/cancel",
            ['description' => 'sick or unwell']
        );

        $response->assertStatus(Response::HTTP_BAD_REQUEST)
            ->assertJson(fn (AssertableJson $json) => $json
                ->whereAllType([
                    'apiVersion' => 'string',
                    'error' => 'array',
                ]));
    }

    public function testAuthenticatedUserCanNotCancelScheduleWithApi(): void
    {
        Sanctum::actingAs($this->user, [TokenAbilityEnum::APIAccess()]);
        $response = $this->postJson('/api/v0/schedule-employees/1/cancel');

        $response->assertStatus(Response::HTTP_FORBIDDEN)
            ->assertJson(fn (AssertableJson $json) => $json
                ->whereAllType([
                    'apiVersion' => 'string',
                    'error' => 'array',
                ]));
    }
}
