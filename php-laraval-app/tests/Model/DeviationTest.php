<?php

namespace Tests\Model;

use App\Models\Deviation;
use App\Models\Schedule;
use App\Models\User;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class DeviationTest extends TestCase
{
    /** @test */
    public function deviationsDatabaseHasExpectedColumns(): void
    {
        $this->assertTrue(
            Schema::hasColumns('deviations', [
                'id',
                'user_id',
                'schedule_id',
                'type',
                'reason',
                'is_handled',
                'created_at',
                'updated_at',
                'deleted_at',
            ]),
        );
    }

    /** @test */
    public function deviationHasUser(): void
    {
        $deviation = Deviation::first();

        if ($deviation) {
            $this->assertInstanceOf(User::class, $deviation->user);
        } else {
            $this->assertNull($deviation);
        }
    }

    /** @test */
    public function deviationHasSchedule(): void
    {
        $deviation = Deviation::first();

        if ($deviation) {
            $this->assertInstanceOf(Schedule::class, $deviation->schedule);
        } else {
            $this->assertNull($deviation);
        }
    }
}
