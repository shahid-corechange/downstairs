<?php

namespace Tests\Model;

use App\Enums\ScheduleEmployee\ScheduleEmployeeStatusEnum;
use App\Models\ScheduleEmployee;
use App\Models\User;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ScheduleEmployeeTest extends TestCase
{
    /** @test */
    public function scheduleEmployeesDatabaseHasExpectedColumns(): void
    {
        $this->assertTrue(
            Schema::hasColumns('schedule_employees', [
                'id',
                'scheduleable_type',
                'scheduleable_id',
                'user_id',
                'start_latitude',
                'start_longitude',
                'start_ip',
                'start_at',
                'end_latitude',
                'end_longitude',
                'end_ip',
                'end_at',
                'description',
                'status',
                'created_at',
                'updated_at',
                'deleted_at',
            ]),
        );
    }

    /** @test */
    public function scheduleEmployeeHasTotalWorkTime(): void
    {
        $scheduleEmployee = ScheduleEmployee::where('status', ScheduleEmployeeStatusEnum::Done())
            ->first();

        if ($scheduleEmployee) {
            $this->assertIsInt($scheduleEmployee->total_work_time);
        } else {
            $this->assertNull($scheduleEmployee);
        }
    }

    /** @test */
    public function scheduleEmployeeHasScheduleable(): void
    {
        $scheduleEmployee = ScheduleEmployee::first();

        if ($scheduleEmployee) {
            $this->assertIsObject($scheduleEmployee->schedule);
        } else {
            $this->assertNull($scheduleEmployee);
        }
    }

    /** @test */
    public function scheduleEmployeeHasUser(): void
    {
        $scheduleEmployee = ScheduleEmployee::first();

        if ($scheduleEmployee) {
            $this->assertInstanceOf(User::class, $scheduleEmployee->user);
        } else {
            $this->assertNull($scheduleEmployee);
        }
    }
}
