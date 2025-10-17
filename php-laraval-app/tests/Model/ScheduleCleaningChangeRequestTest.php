<?php

namespace Tests\Model;

use App\Enums\ScheduleCleaning\ScheduleCleaningStatusEnum;
use App\Models\ScheduleCleaning;
use App\Models\ScheduleCleaningChangeRequest;
use App\Models\User;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ScheduleCleaningChangeRequestTest extends TestCase
{
    // protected ScheduleCleaningChangeRequest $request;

    // protected function setUp(): void
    // {
    //     parent::setUp();

    //     $this->request = ScheduleCleaningChangeRequest::create([
    //         'schedule_cleaning_id' => ScheduleCleaning::first()->id,
    //         'causer_id' => $this->admin->id,
    //         'start_at_changed' => now(),
    //         'end_at_changed' => now()->addHour(),
    //         'status' => ScheduleCleaningStatusEnum::Pending(),
    //     ]);
    // }

    // /** @test */
    // public function scheduleCleaningChangeRequestsDatabaseHasExpectedColumns(): void
    // {
    //     $this->assertTrue(
    //         Schema::hasColumns('schedule_cleaning_change_requests', [
    //             'id',
    //             'schedule_cleaning_id',
    //             'causer_id',
    //             'original_start_at',
    //             'start_at_changed',
    //             'original_end_at',
    //             'end_at_changed',
    //             'status',
    //             'created_at',
    //             'updated_at',
    //             'deleted_at',
    //         ]),
    //     );
    // }

    // /** @test */
    // public function scheduleCleaningChangeRequestHasSchedule(): void
    // {
    //     $this->assertInstanceOf(
    //         ScheduleCleaning::class,
    //         $this->request->schedule
    //     );
    // }

    // /** @test */
    // public function scheduleCleaningChangeRequestHasCauser(): void
    // {
    //     $this->assertInstanceOf(
    //         User::class,
    //         $this->request->causer
    //     );
    // }
}
