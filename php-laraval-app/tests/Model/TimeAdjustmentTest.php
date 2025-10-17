<?php

namespace Tests\Model;

use App\Models\ScheduleEmployee;
use App\Models\TimeAdjustment;
use App\Models\User;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class TimeAdjustmentTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // $schedule = ScheduleEmployee::where('work_hour_id', '!=', null)
        //     ->first();

        // $schedule->timeAdjustment()->updateOrCreate(
        //     ['schedule_employee_id' => $schedule->id],
        //     [
        //         'quarters' => fake()->numberBetween(-1, 4),
        //         'reason' => fake()->sentence(),
        //         'causer_id' => $this->admin->id,
        //     ]
        // );
    }

    /** @test */
    public function timeAdjustmentsDatabaseHasExpectedColumns(): void
    {
        $this->assertTrue(
            Schema::hasColumns('time_adjustments', [
                'id',
                'schedule_employee_id',
                'causer_id',
                'quarters',
                'reason',
                'created_at',
                'updated_at',
            ]),
        );
    }

    // /** @test */
    // public function timeAdjustmentHasScheduleEmployee(): void
    // {
    //     $timeAdjustment = TimeAdjustment::first();

    //     $this->assertInstanceOf(ScheduleEmployee::class, $timeAdjustment->schedule);
    // }

    // /** @test */
    // public function timeAdjustmentHasCauser(): void
    // {
    //     $timeAdjustment = TimeAdjustment::first();

    //     $this->assertInstanceOf(User::class, $timeAdjustment->causer);
    // }
}
