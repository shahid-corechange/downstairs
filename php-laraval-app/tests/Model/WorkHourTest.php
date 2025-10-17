<?php

namespace Tests\Model;

use App\Models\ScheduleEmployee;
use App\Models\User;
use App\Models\WorkHour;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class WorkHourTest extends TestCase
{
    /** @test */
    public function workHoursDatabaseHasExpectedColumns(): void
    {
        $this->assertTrue(
            Schema::hasColumns('work_hours', [
                'id',
                'user_id',
                'fortnox_attendance_id',
                'date',
                'start_time',
                'end_time',
                'created_at',
                'updated_at',
            ]),
        );
    }

    //     /** @test */
    //     public function workHourHasUser(): void
    //     {
    //         $workHour = WorkHour::first();

    //         $this->assertInstanceOf(User::class, $workHour->user);
    //     }

    //     /** @test */
    //     public function workHourHasScheduleEmployees(): void
    //     {
    //         $workHour = WorkHour::whereHas('schedules')->first();
    //         $this->assertInstanceOf(ScheduleEmployee::class, $workHour->schedules->first());
    //     }

    //     /** @test */
    //     public function workHourHasWorkHours(): void
    //     {
    //         $workHour = WorkHour::first();
    //         $this->assertIsFloat($workHour->work_hours);
    //     }

    //     /** @test */
    //     public function workHourHasTimeAdjustmentHours(): void
    //     {
    //         $workHour = WorkHour::first();
    //         $this->assertIsFloat($workHour->time_adjustment_hours);
    //     }

    //     /** @test */
    //     public function workHourHasTotalHours(): void
    //     {
    //         $workHour = WorkHour::first();
    //         $this->assertIsFloat($workHour->total_hours);
    //     }

    //     /** @test */
    //     public function workHourHasHasDeviation(): void
    //     {
    //         $workHour = WorkHour::first();
    //         $this->assertIsBool($workHour->has_deviation);
    //     }

    //     /** @test */
    //     public function workHourHasBookingHours(): void
    //     {
    //         $workHour = WorkHour::first();
    //         $this->assertIsFloat($workHour->booking_hours);
    //     }

    //     /** @test */
    //     public function workHourHasUnapprovedHours(): void
    //     {
    //         $workHour = WorkHour::first();
    //         $this->assertIsNumeric($workHour->unapproved_hours);
    //     }
}
