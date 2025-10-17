<?php

namespace Tests\Model;

use App\Enums\ScheduleCleaning\ScheduleCleaningStatusEnum;
use App\Models\Customer;
use App\Models\CustomTask;
use App\Models\Order;
use App\Models\Property;
use App\Models\ScheduleCleaning;
use App\Models\ScheduleCleaningDeviation;
use App\Models\ScheduleCleaningProduct;
use App\Models\ScheduleCleaningTask;
use App\Models\ScheduleEmployee;
use App\Models\Subscription;
use App\Models\Team;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ScheduleCleaningTest extends TestCase
{
    // /** @test */
    // public function scheduleCleaningsDatabaseHasExpectedColumns(): void
    // {
    //     $this->assertTrue(
    //         Schema::hasColumns('schedule_cleanings', [
    //             'id',
    //             'subscription_id',
    //             'team_id',
    //             'customer_id',
    //             'property_id',
    //             'status',
    //             'start_at',
    //             'end_at',
    //             'original_start_at',
    //             'quarters',
    //             'is_fixed',
    //             'key_information',
    //             'note',
    //             'cancelable_type',
    //             'cancelable_id',
    //             'created_at',
    //             'updated_at',
    //             'deleted_at',
    //         ]),
    //     );
    // }

    // /** @test */
    // public function scheduleCleaningHasActualStartAt(): void
    // {
    //     $scheduleCleaning = ScheduleCleaning::where('status', ScheduleCleaningStatusEnum::Done())
    //         ->first();

    //     $this->assertNotEmpty($scheduleCleaning->actual_start_at);
    // }

    // /** @test */
    // public function scheduleCleaningHasActualEndAt(): void
    // {
    //     $scheduleCleaning = ScheduleCleaning::where('status', ScheduleCleaningStatusEnum::Done())
    //         ->first();

    //     $this->assertNotEmpty($scheduleCleaning->actual_end_at);
    // }

    // /** @test */
    // public function scheduleCleaningHasActualQuarters(): void
    // {
    //     $scheduleCleaning = ScheduleCleaning::where('status', ScheduleCleaningStatusEnum::Done())
    //         ->first();

    //     $this->assertNotEmpty($scheduleCleaning->actual_quarters);
    // }

    // /** @test */
    // public function scheduleCleaningHasCalendarQuarters(): void
    // {
    //     $scheduleCleaning = ScheduleCleaning::where('status', ScheduleCleaningStatusEnum::Done())
    //         ->first();

    //     $this->assertNotEmpty($scheduleCleaning->calendar_quarters);
    // }

    // /** @test */
    // public function scheduleCleaningHasDeviationAtribute(): void
    // {
    //     $scheduleCleaning = ScheduleCleaning::where('status', ScheduleCleaningStatusEnum::Done())
    //         ->first();

    //     $this->assertIsBool($scheduleCleaning->has_deviation);
    // }

    // /** @test */
    // public function scheduleCleaningCanGetProductSummaries(): void
    // {
    //     $scheduleCleaning = ScheduleCleaning::where('status', ScheduleCleaningStatusEnum::Done())
    //         ->first();

    //     $this->assertIsArray($scheduleCleaning->productSummaries());
    // }

    // /** @test */
    // public function scheduleCleaningHasSubscription(): void
    // {
    //     $scheduleCleaning = ScheduleCleaning::first();

    //     $this->assertInstanceOf(Subscription::class, $scheduleCleaning->subscription);
    // }

    // /** @test */
    // public function scheduleCleaningHasTeam(): void
    // {
    //     $scheduleCleaning = ScheduleCleaning::first();

    //     $this->assertInstanceOf(Team::class, $scheduleCleaning->team);
    // }

    // /** @test */
    // public function scheduleCleaningHasCustomer(): void
    // {
    //     $scheduleCleaning = ScheduleCleaning::first();

    //     $this->assertInstanceOf(Customer::class, $scheduleCleaning->customer);
    // }

    // /** @test */
    // public function scheduleCleaningHasProperty(): void
    // {
    //     $scheduleCleaning = ScheduleCleaning::first();

    //     $this->assertInstanceOf(Property::class, $scheduleCleaning->property);
    // }

    // /** @test */
    // public function scheduleCleaningHasScheduleEmployees(): void
    // {
    //     $scheduleCleaning = ScheduleCleaning::first();

    //     $this->assertIsObject($scheduleCleaning->scheduleEmployees);
    //     $this->assertInstanceOf(ScheduleEmployee::class, $scheduleCleaning->scheduleEmployees->first());
    // }

    // /** @test */
    // public function scheduleCleaningHasScheduleProducts(): void
    // {
    //     $product = ScheduleCleaningProduct::first();
    //     $scheduleCleaning = $product->schedule;

    //     $this->assertIsObject($scheduleCleaning->products);
    //     $this->assertInstanceOf(ScheduleCleaningProduct::class, $scheduleCleaning->products->first());
    // }

    // /** @test */
    // public function scheduleCleaningHasScheduleTasks(): void
    // {
    //     $scheduleCleaning = ScheduleCleaning::first();
    //     $scheduleCleaning->tasks()->create([]);

    //     $this->assertIsObject($scheduleCleaning->tasks);
    //     $this->assertInstanceOf(CustomTask::class, $scheduleCleaning->tasks->first());
    // }

    // /** @test */
    // public function scheduleCleaningHasOrder(): void
    // {
    //     $scheduleCleaning = ScheduleCleaning::where('status', ScheduleCleaningStatusEnum::Done())
    //         ->first();

    //     $this->assertInstanceOf(Order::class, $scheduleCleaning->order);
    // }

    // /** @test */
    // public function scheduleCleaningHasDeviation(): void
    // {
    //     $deviation = ScheduleCleaningDeviation::first();
    //     $scheduleCleaning = $deviation->scheduleCleaning;

    //     $this->assertInstanceOf(ScheduleCleaningDeviation::class, $scheduleCleaning->deviation);
    // }

    // public function scheduleCleaningHasScheduleCleaningTasks(): void
    // {
    //     $scheduleCleaning = ScheduleCleaning::where('status', ScheduleCleaningStatusEnum::Done())->first();
    //     $scheduleCleaning->scheduleCleaningTasks()
    //         ->create([
    //             'custom_task_id' => $scheduleCleaning->tasks->first()->id,
    //             'is_completed' => true,
    //         ]);

    //     $this->assertIsObject($scheduleCleaning->scheduleCleaningTasks);
    //     $this->assertInstanceOf(ScheduleCleaningTask::class, $scheduleCleaning->scheduleCleaningTasks()->first());
    // }
}
