<?php

namespace Tests\Portal\Employee;

use App\DTOs\Deviation\DeviationResponseDTO;
use App\Enums\Deviation\DeviationTypeEnum;
use App\Jobs\SentWorkingHoursJob;
use App\Models\Deviation;
use App\Models\ScheduleCleaning;
use App\Models\ScheduleEmployee;
use Bus;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class EmployeeDeviationTest extends TestCase
{
    public function testAdminCanAccessEmployeeDeviations(): void
    {
        $pageSize = config('downstairs.pageSize');
        $count = Deviation::count();
        $total = $count > $pageSize ? $pageSize : $count;

        $this->actingAs($this->admin)
            ->get('/deviations/employee')
            ->assertInertia(fn (Assert $page) => $page
                ->component('Deviation/Employee/index')
                ->has('deviations', $total)
                ->has('deviations.0', fn (Assert $page) => $page
                    ->has('id')
                    ->has('type')
                    ->has('reason')
                    ->has('isHandled')
                    ->etc()
                    ->has('user', fn (Assert $page) => $page
                        ->has('id')
                        ->has('fullname')
                        ->etc())
                    ->has('scheduleCleaning', fn (Assert $page) => $page
                        ->has('endAt')
                        ->etc()))
                ->has('pagination', fn (Assert $page) => $page
                    ->where('total', $count)
                    ->where('size', $pageSize)
                    ->where('currentPage', 1)
                    ->etc()));
    }

    public function testCustomerCanNotAccessEmployeeDeviations(): void
    {
        $this->actingAs($this->user)
            ->get('/deviations/employee')
            ->assertInertia(fn (Assert $page) => $page
                ->component('Error/index')
                ->where('code', '404'));
    }

    public function testCanFilterEmployeeDeviations(): void
    {
        $data = Deviation::first();
        $pageSize = config('downstairs.pageSize');

        $this->actingAs($this->admin)
            ->get("/deviations/employee?id.eq={$data->id}")
            ->assertInertia(fn (Assert $page) => $page
                ->component('Deviation/Employee/index')
                ->has('deviations', 1)
                ->has('deviations.0', fn (Assert $page) => $page
                    ->where('id', $data->id)
                    ->where('type', $data->type)
                    ->where('reason', $data->reason)
                    ->where('isHandled', $data->is_handled)
                    ->etc()
                    ->has('user', fn (Assert $page) => $page
                        ->where('id', $data->user_id)
                        ->where('fullname', $data->user->fullname)
                        ->etc())
                    ->has('scheduleCleaning', fn (Assert $page) => $page
                        ->has('endAt')
                        ->etc()))
                ->has('pagination', fn (Assert $page) => $page
                    ->where('total', 1)
                    ->where('size', $pageSize)
                    ->where('currentPage', 1)
                    ->etc()));
    }

    public function testAdminCanAccessEmployeeDeviationsJson(): void
    {
        $response = $this->actingAs($this->admin)
            ->get('/deviations/employee/json');
        $keys = array_keys(
            DeviationResponseDTO::from(Deviation::first())->toArray()
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

    public function testCanHandleEmployeeDeviation(): void
    {
        $deviation = Deviation::first();

        $this->actingAs($this->admin)
            ->post("/deviations/employee/{$deviation->id}/handle")
            ->assertStatus(302)
            ->assertRedirect()
            ->assertSessionHas('success', __('deviation handled successfully'));

        $this->assertDatabaseHas('deviations', [
            'id' => $deviation->id,
            'is_handled' => true,
        ]);
    }

    public function testCanUpdateWorkerAttendanceWithoutTimeAdjustment(): void
    {
        $deviation = Deviation::first();
        $deviation->update(['type' => DeviationTypeEnum::StartWrongTime()]);

        $data = [
            'startAt' => now()->addHour()->format('Y-m-d H:i:s'),
            'endAt' => now()->addHours(2)->format('Y-m-d H:i:s'),
        ];

        $this->actingAs($this->admin)
            ->patch("/deviations/employee/{$deviation->id}/attendance", $data)
            ->assertStatus(302)
            ->assertRedirect()
            ->assertSessionHas('success', __('worker attendance updated successfully'));

        $scheduleEmployee = $deviation->scheduleCleaning->scheduleEmployees()
            ->where('user_id', $deviation->user_id)
            ->first();

        $this->assertDatabaseHas('deviations', [
            'id' => $deviation->id,
            'is_handled' => true,
        ]);

        $this->assertDatabaseHas('schedule_employees', [
            'id' => $scheduleEmployee->id,
            'start_at' => $data['startAt'],
            'end_at' => $data['endAt'],
        ]);

        $this->assertDatabaseMissing('time_adjustments', [
            'schedule_employee_id' => $scheduleEmployee->id,
        ]);
    }

    public function testCanUpdateWorkerAttendanceWithTimeAdjustment(): void
    {
        $deviation = Deviation::first();
        $deviation->update(['type' => DeviationTypeEnum::StartWrongTime()]);
        /** @var ScheduleCleaning */
        $cleaning = $deviation->scheduleCleaning;
        $workTime = $cleaning->scheduleEmployees->sum('total_work_time');
        $quarters = ceil($workTime / (60 * 15));
        $quarters = $quarters > $cleaning->quarters ? $cleaning->quarters : $quarters;

        $data = [
            'startAt' => now()->addHour()->format('Y-m-d H:i:s'),
            'endAt' => now()->addHours(2)->format('Y-m-d H:i:s'),
            'timeAdjustment' => [
                'quarters' => $cleaning->quarters - $quarters,
                'reason' => 'Test',
            ],
        ];

        $this->actingAs($this->admin)
            ->patch("/deviations/employee/{$deviation->id}/attendance", $data)
            ->assertStatus(302)
            ->assertRedirect()
            ->assertSessionHas('success', __('worker attendance updated successfully'));

        $scheduleEmployee = $deviation->scheduleCleaning->scheduleEmployees()
            ->where('user_id', $deviation->user_id)
            ->first();

        $this->assertDatabaseHas('deviations', [
            'id' => $deviation->id,
            'is_handled' => true,
        ]);

        $this->assertDatabaseHas('schedule_employees', [
            'id' => $scheduleEmployee->id,
            'start_at' => $data['startAt'],
            'end_at' => $data['endAt'],
        ]);

        $this->assertDatabaseHas('time_adjustments', [
            'schedule_employee_id' => $scheduleEmployee->id,
            'quarters' => $data['timeAdjustment']['quarters'],
            'reason' => $data['timeAdjustment']['reason'],
        ]);

        Bus::assertDispatchedAfterResponse(SentWorkingHoursJob::class);
    }

    public function testCanNotUpdateWorkerAttendanceIfQuartersLessThanZero(): void
    {
        $deviation = Deviation::first();
        $deviation->update(['type' => DeviationTypeEnum::StartWrongTime()]);
        $startAt = now()->addHour();
        $endAt = now()->addHours(2);
        $workQuarters = $startAt->diffInMinutes($endAt) / 15;

        $data = [
            'startAt' => now()->addHour()->format('Y-m-d H:i:s'),
            'endAt' => now()->addHours(2)->format('Y-m-d H:i:s'),
            'timeAdjustment' => [
                'quarters' => -($workQuarters + 1),
                'reason' => 'Test',
            ],
        ];

        $this->actingAs($this->admin)
            ->patch("/deviations/employee/{$deviation->id}/attendance", $data)
            ->assertStatus(302)
            ->assertRedirect()
            ->assertSessionHas('error', __('total quarters employee worked on cannot be less than 0'));

        $this->assertDatabaseHas('deviations', [
            'id' => $deviation->id,
            'is_handled' => false,
        ]);

        /** @var ScheduleEmployee $scheduleEmployee */
        $scheduleEmployee = $deviation->scheduleCleaning->scheduleEmployees()
            ->where('user_id', $deviation->user_id)
            ->first();

        $this->assertDatabaseHas('schedule_employees', [
            'id' => $scheduleEmployee->id,
            'start_at' => $scheduleEmployee->start_at,
            'end_at' => $scheduleEmployee->end_at,
        ]);

        $this->assertDatabaseMissing('time_adjustments', [
            'schedule_employee_id' => $scheduleEmployee->id,
        ]);

        Bus::assertNotDispatched(SentWorkingHoursJob::class);
    }
}
