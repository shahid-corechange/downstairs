<?php

namespace Tests\Portal\Operation;

use App\Enums\ScheduleCleaning\ScheduleCleaningChangeStatusEnum;
use App\Models\ScheduleCleaning;
use App\Models\ScheduleCleaningChangeRequest;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ChangeRequestHistoryTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $schedules = ScheduleCleaning::limit(5)->inRandomOrder()->get();

        foreach ($schedules as $schedule) {
            $schedule->changeRequest()->create([
                'causer_id' => User::role('Employee')->get()->random()->id,
                'original_start_at' => $schedule->start_at,
                'start_at_changed' => $schedule->start_at->addHours(1),
                'original_end_at' => $schedule->end_at,
                'end_at_changed' => $schedule->end_at->addHours(1),
                'status' => ScheduleCleaningChangeStatusEnum::Approved(),
            ]);
        }
    }

    public function testAdminCanAccessChangeRequestsHistories(): void
    {
        $pageSize = config('downstairs.pageSize');
        $count = ScheduleCleaningChangeRequest::whereIn(
            'status',
            [
                ScheduleCleaningChangeStatusEnum::Approved(),
                ScheduleCleaningChangeStatusEnum::Rejected(),
            ],
        )->count();
        $total = $count > $pageSize ? $pageSize : $count;

        $this->actingAs($this->admin)
            ->get('/schedules/change-requests/histories')
            ->assertInertia(fn (Assert $page) => $page
                ->component('Schedule/ChangeRequest/History/index')
                ->has('changeRequests', $total)
                ->has('changeRequests.0', fn (Assert $page) => $page
                    ->has('id')
                    ->has('scheduleId')
                    ->has('originalStartAt')
                    ->has('startAtChanged')
                    ->has('originalEndAt')
                    ->has('endAtChanged')
                    ->has('status')
                    ->etc()
                    ->has('schedule', fn (Assert $page) => $page
                        ->has('team')
                        ->has('property')
                        ->has('status')
                        ->etc())
                    ->has('causer', fn (Assert $page) => $page
                        ->has('fullname'))));
    }

    public function testCustomerCanNotAccessChangeRequestsHistories(): void
    {
        $this->actingAs($this->user)
            ->get('/schedules/change-requests/histories')
            ->assertInertia(fn (Assert $page) => $page
                ->component('Error/index')
                ->where('code', '404'));
    }

    public function testCanFilterChangeRequestsHistories(): void
    {
        $data = ScheduleCleaningChangeRequest::whereIn(
            'status',
            [
                ScheduleCleaningChangeStatusEnum::Approved(),
                ScheduleCleaningChangeStatusEnum::Rejected(),
            ],
        )->first();

        $this->actingAs($this->admin)
            ->get("/schedules/change-requests/histories?id.eq={$data->id}")
            ->assertInertia(fn (Assert $page) => $page
                ->component('Schedule/ChangeRequest/History/index')
                ->has('changeRequests', 1)
                ->has('changeRequests.0', fn (Assert $page) => $page
                    ->where('id', $data->id)
                    ->where('scheduleId', $data->schedule_cleaning_id)
                    ->has('originalStartAt')
                    ->has('startAtChanged')
                    ->has('originalEndAt')
                    ->has('endAtChanged')
                    ->etc()
                    ->has('schedule', fn (Assert $page) => $page
                        ->has('team')
                        ->has('property')
                        ->etc())
                    ->has('causer', fn (Assert $page) => $page
                        ->where('fullname', $data->causer->fullname))));
    }

    public function testAdminCanAccessChangeRequestsHistoriesJson(): void
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
}
