<?php

namespace Tests\Portal\Employee;

use App\DTOs\LeaveRegistration\LeaveRegistrationResponseDTO;
use App\DTOs\ScheduleEmployee\ScheduleEmployeeResponseDTO;
use App\Enums\LeaveRegistration\AbsenceTypeEnum;
use App\Jobs\SendAbsenceTransactionsJob;
use App\Models\LeaveRegistration;
use App\Models\ScheduleEmployee;
use App\Services\LeaveRegistrationService;
use Bus;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class LeaveRegistrationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        LeaveRegistration::factory(1)->create();
    }

    public function testAdminCanAccessLeaveRegistrations(): void
    {
        $pageSize = config('downstairs.pageSize');
        $count = LeaveRegistration::count();
        $total = $count > $pageSize ? $pageSize : $count;

        $this->actingAs($this->admin)
            ->get('/leave-registrations')
            ->assertInertia(fn (Assert $page) => $page
                ->component('LeaveRegistration/Overview/index')
                ->has('leaveRegistrations', $total)
                ->has('leaveRegistrations.0', fn (Assert $page) => $page
                    ->has('id')
                    ->has('employeeId')
                    ->has('type')
                    ->has('startAt')
                    ->has('endAt')
                    ->has('isStopped')
                    ->has('rescheduleNeeded')
                    ->etc()
                    ->has('employee', fn (Assert $page) => $page
                        ->has('name')
                        ->etc()))
                ->has('pagination', fn (Assert $page) => $page
                    ->where('total', $count)
                    ->where('size', $pageSize)
                    ->where('currentPage', 1)
                    ->etc()));
    }

    public function testCustomerCanNotAccessLeaveRegistrations(): void
    {
        $this->actingAs($this->user)
            ->get('/leave-registrations')
            ->assertInertia(fn (Assert $page) => $page
                ->component('Error/index')
                ->where('code', '404'));
    }

    public function testCanFilterLeaveRegistrations(): void
    {
        $data = LeaveRegistration::first();
        $pageSize = config('downstairs.pageSize');

        $this->actingAs($this->admin)
            ->get("/leave-registrations?id.eq={$data->id}")
            ->assertInertia(fn (Assert $page) => $page
                ->component('LeaveRegistration/Overview/index')
                ->has('leaveRegistrations', 1)
                ->has('leaveRegistrations.0', fn (Assert $page) => $page
                    ->has('id')
                    ->has('employeeId')
                    ->has('type')
                    ->has('startAt')
                    ->has('endAt')
                    ->has('isStopped')
                    ->has('rescheduleNeeded')
                    ->etc()
                    ->has('employee', fn (Assert $page) => $page
                        ->has('name')
                        ->etc()))
                ->has('pagination', fn (Assert $page) => $page
                    ->where('total', 1)
                    ->where('size', $pageSize)
                    ->where('currentPage', 1)
                    ->etc()));
    }

    public function testAdminCanAccessLeaveRegistrationsJson(): void
    {
        $response = $this->actingAs($this->admin)
            ->get('/leave-registrations/json');
        $keys = array_keys(
            LeaveRegistrationResponseDTO::from(LeaveRegistration::first())->toArray()
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

    public function testAdminCanAccessLeaveRegistrationSchedulessJson(): void
    {
        $leaveRegistration = LeaveRegistration::first();
        $response = $this->actingAs($this->admin)
            ->get("/leave-registrations/{$leaveRegistration->id}/schedules/json");
        $keys = array_keys(
            ScheduleEmployeeResponseDTO::from(ScheduleEmployee::first())->toArray()
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

    public function testCanCreateLeaveRegistration(): void
    {
        $data = [
            'employeeId' => 1,
            'type' => AbsenceTypeEnum::SickLeave(),
            'startAt' => now()->toDateString(),
            'endAt' => now()->addDay()->toDateString(),
        ];

        $this->actingAs($this->admin)
            ->post('/leave-registrations', $data)
            ->assertStatus(302)
            ->assertRedirect()
            ->assertSessionHas('success', __('leave registration created successfully'));

        $this->assertDatabaseHas('leave_registrations', [
            'employee_id' => $data['employeeId'],
            'type' => $data['type'],
            'start_at' => $data['startAt'],
            'end_at' => $data['endAt'],
        ]);
    }

    public function testCanCreateHistoricalLeaveRegistration(): void
    {
        $data = [
            'employeeId' => 1,
            'type' => AbsenceTypeEnum::SickLeave(),
            'startAt' => now()->subDays(2)->toDateString(),
            'endAt' => now()->subDays(1)->toDateString(),
        ];

        $this->actingAs($this->admin)
            ->post('/leave-registrations', $data)
            ->assertStatus(302)
            ->assertRedirect()
            ->assertSessionHas('success', __('leave registration created successfully'));

        $this->assertDatabaseHas('leave_registrations', [
            'employee_id' => $data['employeeId'],
            'type' => $data['type'],
            'start_at' => $data['startAt'],
            'end_at' => $data['endAt'],
        ]);
    }

    public function testCanUpdateLeaveRegistration(): void
    {
        $data = [
            'type' => AbsenceTypeEnum::SickLeave(),
            'startAt' => now()->toDateString(),
            'endAt' => now()->addDay()->toDateString(),
        ];

        $leaveRegistration = LeaveRegistration::first();

        $this->actingAs($this->admin)
            ->patch("/leave-registrations/$leaveRegistration->id", $data)
            ->assertStatus(302)
            ->assertRedirect()
            ->assertSessionHas('success', __('leave registration updated successfully'));

        $this->assertDatabaseHas('leave_registrations', [
            'id' => $leaveRegistration->id,
            'employee_id' => $leaveRegistration->employee_id,
            'type' => $data['type'],
            'start_at' => $data['startAt'],
            'end_at' => $data['endAt'],
        ]);
    }

    public function testCanDeleteLeaveRegistration(): void
    {
        $leaveRegistration = LeaveRegistration::first();

        $this->actingAs($this->admin)
            ->delete("/leave-registrations/{$leaveRegistration->id}")
            ->assertStatus(302)
            ->assertRedirect()
            ->assertSessionHas('success', __('leave registration deleted successfully'));

        $this->assertSoftDeleted('leave_registrations', [
            'id' => $leaveRegistration->id,
        ]);
    }

    public function testCanStopLeaveRegistration(): void
    {
        $leaveRegistration = LeaveRegistration::first();
        $details = LeaveRegistrationService::generateDetails($leaveRegistration);

        $this->actingAs($this->admin)
            ->post("/leave-registrations/{$leaveRegistration->id}/stop")
            ->assertStatus(302)
            ->assertRedirect()
            ->assertSessionHas('success', __('leave registration stopped successfully'));

        Bus::assertDispatchedAfterResponse(SendAbsenceTransactionsJob::class);

        $this->assertDatabaseHas('leave_registrations', [
            'id' => $leaveRegistration->id,
            'is_stopped' => true,
        ]);

        foreach ($details as $detail) {
            $this->assertDatabaseHas('leave_registration_details', [
                'leave_registration_id' => $leaveRegistration->id,
                'start_at' => $detail['start_at']->toIsoString(),
                'end_at' => $detail['end_at']->toIsoString(),
            ]);
        }
    }

    public function testCanNotStopLeaveRegistrationIfAlreadyStopped(): void
    {
        $leaveRegistration = LeaveRegistration::first();
        $leaveRegistration->update(['is_stopped' => true]);

        $this->actingAs($this->admin)
            ->post("/leave-registrations/{$leaveRegistration->id}/stop")
            ->assertStatus(302)
            ->assertRedirect()
            ->assertSessionHas('error', __('leave registration already stopped'));
    }
}
