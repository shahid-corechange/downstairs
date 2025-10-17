<?php

namespace Tests\Model;

use App\Models\CustomTask;
use App\Models\ScheduleCleaning;
use App\Models\ScheduleCleaningTask;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ScheduleCleaningTaskTest extends TestCase
{
    // /** @test */
    // public function scheduleCleaningTasksDatabaseHasExpectedColumns(): void
    // {
    //     $this->assertTrue(
    //         Schema::hasColumns('schedule_cleaning_tasks', [
    //             'id',
    //             'custom_task_id',
    //             'schedule_cleaning_id',
    //             'is_completed',
    //         ]),
    //     );
    // }

    // /** @test */
    // public function scheduleCleaningTaskHasSchedule(): void
    // {
    //     $task = ScheduleCleaningTask::first();

    //     $this->assertInstanceOf(ScheduleCleaning::class, $task->schedule);
    // }

    // /** @test */
    // public function scheduleCleaningTaskHasCustomTask(): void
    // {
    //     $task = ScheduleCleaningTask::first();

    //     $this->assertInstanceOf(CustomTask::class, $task->customTask);
    // }
}
