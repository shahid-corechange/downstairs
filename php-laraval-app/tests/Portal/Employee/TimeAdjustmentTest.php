<?php

namespace Tests\Portal\Employee;

use App\DTOs\TimeAdjustment\TimeAdjustmentResponseDTO;
use App\Jobs\UpdateWorkHourJob;
use App\Models\ScheduleEmployee;
use App\Models\TimeAdjustment;
use Bus;
use Tests\TestCase;

class TimeAdjustmentTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $schedule = ScheduleEmployee::where('work_hour_id', '!=', null)
            ->first();

        $schedule->timeAdjustment()->updateOrCreate(
            ['schedule_employee_id' => $schedule->id],
            [
                'quarters' => fake()->numberBetween(-1, 4),
                'reason' => fake()->sentence(),
                'causer_id' => $this->admin->id,
            ]
        );
    }

    public function testAdminCanAccessTimeAdjustmensJson(): void
    {
        $response = $this->actingAs($this->admin)
            ->get('/time-adjustments/json');
        $timeAdjustment = TimeAdjustment::first();
        $keys = array_keys(
            TimeAdjustmentResponseDTO::from($timeAdjustment)->toArray()
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

    public function testAdminCanCreateTimeAdjustment(): void
    {
        $schedule = ScheduleEmployee::where('work_hour_id', '!=', null)
            ->first();
        $schedule->timeAdjustment()->delete();
        $data = [
            'scheduleEmployeeId' => $schedule->id,
            'quarters' => fake()->numberBetween(-1, 4),
            'reason' => fake()->sentence(),
        ];

        $response = $this->actingAs($this->admin)
            ->post('/time-adjustments', $data)
            ->assertStatus(302)
            ->assertRedirect()
            ->assertSessionHas('success', __('time adjustment created successfully'));

        $this->assertDatabaseHas('time_adjustments', [
            'schedule_employee_id' => $data['scheduleEmployeeId'],
            'causer_id' => $this->admin->id,
            'quarters' => $data['quarters'],
            'reason' => $data['reason'],
        ]);

        Bus::assertDispatchedAfterResponse(UpdateWorkHourJob::class);
    }

    public function testAdminCanUpdateTimeAdjustment(): void
    {
        $timeAdjustment = TimeAdjustment::first();
        $data = [
            'quarters' => fake()->numberBetween(-1, 4),
            'reason' => fake()->sentence(),
        ];

        $response = $this->actingAs($this->admin)
            ->put("/time-adjustments/{$timeAdjustment->id}", $data);

        $response->assertRedirect();
        $response->assertSessionHas('success', __('time adjustment updated successfully'));

        $this->assertDatabaseHas('time_adjustments', [
            'id' => $timeAdjustment->id,
            'quarters' => $data['quarters'],
            'reason' => $data['reason'],
        ]);

        Bus::assertDispatchedAfterResponse(UpdateWorkHourJob::class);
    }

    public function testCanNotUpdateTimeAdjustmentIfQuartersLessThanZero(): void
    {
        $timeAdjustment = TimeAdjustment::first();
        $data = [
            'quarters' => -($timeAdjustment->schedule->work_quarters + 1),
            'reason' => fake()->sentence(),
        ];

        $this->actingAs($this->admin)
            ->put("/time-adjustments/{$timeAdjustment->id}", $data)
            ->assertStatus(302)
            ->assertRedirect()
            ->assertSessionHas('error', __('total quarters employee worked on cannot be less than 0'));

        $this->assertDatabaseHas('time_adjustments', [
            'id' => $timeAdjustment->id,
            'quarters' => $timeAdjustment->quarters,
            'reason' => $timeAdjustment->reason,
        ]);

        Bus::assertNotDispatched(UpdateWorkHourJob::class);
    }

    public function testAdminCanDeleteTimeAdjustment(): void
    {
        $timeAdjustment = TimeAdjustment::first();

        $response = $this->actingAs($this->admin)
            ->delete("/time-adjustments/{$timeAdjustment->id}");

        $response->assertRedirect();
        $response->assertSessionHas('success', __('time adjustment deleted successfully'));

        $this->assertDatabaseMissing('time_adjustments', [
            'id' => $timeAdjustment->id,
        ]);

        Bus::assertDispatchedAfterResponse(UpdateWorkHourJob::class);
    }
}
