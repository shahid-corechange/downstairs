<?php

namespace Tests\Portal\Operation;

use App\Enums\ScheduleCleaning\ScheduleCleaningChangeStatusEnum;
use App\Jobs\SendNotificationJob;
use App\Models\ScheduleCleaning;
use App\Models\ScheduleCleaningChangeRequest;
use Bus;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ChangeRequestTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        ScheduleCleaningChangeRequest::factory(5)->create();
    }

    public function testAdminCanAccessChangeRequests(): void
    {
        $pageSize = config('downstairs.pageSize');
        $count = ScheduleCleaningChangeRequest::where(
            'status',
            ScheduleCleaningChangeStatusEnum::Pending(),
        )->count();
        $total = $count > $pageSize ? $pageSize : $count;

        $this->actingAs($this->admin)
            ->get('/schedules/change-requests')
            ->assertInertia(fn (Assert $page) => $page
                ->component('Schedule/ChangeRequest/Overview/index')
                ->has('changeRequests', $total)
                ->has('changeRequests.0', fn (Assert $page) => $page
                    ->has('id')
                    ->has('scheduleId')
                    ->has('startAtChanged')
                    ->has('endAtChanged')
                    ->etc()
                    ->has('schedule', fn (Assert $page) => $page
                        ->has('team')
                        ->has('property')
                        ->etc())));
    }

    public function testCustomerCanNotAccessChangeRequests(): void
    {
        $this->actingAs($this->user)
            ->get('/schedules/change-requests')
            ->assertInertia(fn (Assert $page) => $page
                ->component('Error/index')
                ->where('code', '404'));
    }

    public function testCanFilterChangeRequests(): void
    {
        $data = ScheduleCleaningChangeRequest::where(
            'status',
            ScheduleCleaningChangeStatusEnum::Pending(),
        )->first();

        $this->actingAs($this->admin)
            ->get("/schedules/change-requests?id.eq={$data->id}")
            ->assertInertia(fn (Assert $page) => $page
                ->component('Schedule/ChangeRequest/Overview/index')
                ->has('changeRequests', 1)
                ->has('changeRequests.0', fn (Assert $page) => $page
                    ->where('id', $data->id)
                    ->where('scheduleId', $data->schedule_cleaning_id)
                    ->has('startAtChanged')
                    ->has('endAtChanged')
                    ->etc()
                    ->has('schedule', fn (Assert $page) => $page
                        ->has('team')
                        ->has('property')
                        ->etc())));
    }

    public function testAdminCanAccessChangeRequestsJson(): void
    {
        $response = $this->actingAs($this->admin)
            ->get('/schedules/change-requests/json');
        // $keys = array_keys(
        //     ScheduleCleaningChangeResponseDTO::from(
        //         ScheduleCleaningChangeRequest::first()
        //     )->toArray()
        // );

        $response->assertStatus(200);

        // $response->assertJsonStructure([
        //     'data' => [
        //         '*' => $keys,
        //     ],
        //     'meta' => [
        //         'etag',
        //     ],
        // ]);
    }

    public function testCanApproveChangeRequest(): void
    {
        $schedule = ScheduleCleaning::future()->first();
        $changeDate = now()->addYear();
        $changeRequest = ScheduleCleaningChangeRequest::create([
            'schedule_cleaning_id' => $schedule->id,
            'start_at_changed' => $changeDate,
            'end_at_changed' => $changeDate->copy()->addHour(),
            'status' => ScheduleCleaningChangeStatusEnum::Pending(),
        ]);

        $this->actingAs($this->admin)
            ->post("/schedules/change-requests/$changeRequest->id/approve")
            ->assertStatus(302)
            ->assertRedirect()
            ->assertSessionHas('success', __('change request approved'));

        $this->assertDatabaseHas('schedule_cleaning_change_requests', [
            'id' => $changeRequest->id,
            'status' => ScheduleCleaningChangeStatusEnum::Approved(),
        ]);

        $totalWorkers = $schedule->scheduleEmployees->count();
        $totalQuarters = $schedule->calendar_quarters * $totalWorkers;
        $this->assertDatabaseHas('schedule_cleanings', [
            'id' => $schedule->id,
            'start_at' => $changeDate->format('Y-m-d H:i:s'),
            'end_at' => $changeDate->copy()->addHour()->format('Y-m-d H:i:s'),
            'quarters' => $totalQuarters,
        ]);

        Bus::assertDispatchedAfterResponse(SendNotificationJob::class);
    }

    public function testCanNotApproveChangeRequest(): void
    {
        $schedule = ScheduleCleaning::future()->first();
        $schedule2 = ScheduleCleaning::future()
            ->where('id', '!=', $schedule->id)
            ->where('team_id', $schedule->team_id)->first();
        $changeRequest = ScheduleCleaningChangeRequest::create([
            'schedule_cleaning_id' => $schedule->id,
            'start_at_changed' => $schedule2->start_at,
            'end_at_changed' => $schedule2->end_at,
            'status' => ScheduleCleaningChangeStatusEnum::Pending(),
        ]);

        $this->actingAs($this->admin)
            ->post("/schedules/change-requests/$changeRequest->id/approve")
            ->assertStatus(302)
            ->assertRedirect()
            ->assertSessionHas('error', __('change request not possible reschedule'));

        Bus::assertNotDispatched(SendNotificationJob::class);
    }

    public function testCanRejectChangeRequest(): void
    {
        $schedule = ScheduleCleaning::future()->first();
        $changeDate = now()->addDay();
        $changeRequest = ScheduleCleaningChangeRequest::create([
            'schedule_cleaning_id' => $schedule->id,
            'start_at_changed' => $changeDate,
            'end_at_changed' => $changeDate->copy()->addHour(),
            'status' => ScheduleCleaningChangeStatusEnum::Pending(),
        ]);

        $this->actingAs($this->admin)
            ->post("/schedules/change-requests/$changeRequest->id/reject")
            ->assertStatus(302)
            ->assertRedirect()
            ->assertSessionHas('success', __('change request rejected'));

        $this->assertDatabaseHas('schedule_cleaning_change_requests', [
            'id' => $changeRequest->id,
            'status' => ScheduleCleaningChangeStatusEnum::Rejected(),
        ]);

        Bus::assertDispatchedAfterResponse(SendNotificationJob::class);
    }
}
