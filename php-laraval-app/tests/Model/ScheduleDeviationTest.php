<?php

namespace Tests\Model;

use App\Models\Schedule;
use App\Models\ScheduleDeviation;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ScheduleDeviationTest extends TestCase
{
    /** @test */
    public function scheduleCleaningDeviationsDatabaseHasExpectedColumns(): void
    {
        $this->assertTrue(
            Schema::hasColumns('schedule_deviations', [
                'id',
                'schedule_id',
                'types',
                'is_handled',
                'meta',
                'created_at',
                'updated_at',
            ]),
        );
    }

    /** @test */
    public function scheduleDeviationCanFindOrCreate(): void
    {
        $schedule = Schedule::first();
        if ($schedule) {
            $deviation = ScheduleDeviation::findOrCreate($schedule->id);

            $this->assertInstanceOf(Schedule::class, $deviation->schedule);
        } else {
            $this->assertNull($schedule);
        }
    }

    /** @test */
    public function scheduleDeviationHasSchedule(): void
    {
        $deviation = ScheduleDeviation::first();

        if ($deviation) {
            $this->assertInstanceOf(Schedule::class, $deviation->schedule);
        } else {
            $this->assertNull($deviation);
        }
    }
}
