<?php

namespace Tests\Unit;

use App\DTOs\Fortnox\AttendanceTransaction\AttendanceTransactionDTO;
use App\Enums\ScheduleCleaning\ScheduleCleaningStatusEnum;
use App\Models\ScheduleCleaning;
use App\Models\ScheduleEmployee;
use App\Models\WorkHour;
use App\Services\Fortnox\FortnoxEmployeeService;
use App\Services\WorkHourService;
use Mockery\MockInterface;
use Tests\TestCase;

class WorkHourServiceTest extends TestCase
{
    public function testCanCreateWorkHour(): void
    {
        $mockFortnoxEmployeeService = $this->mock(FortnoxEmployeeService::class, function (MockInterface $mock) {
            $mock->shouldReceive('createAttendanceTransaction')
                ->atMost()
                ->once()
                ->andReturn(
                    AttendanceTransactionDTO::from([
                        'id' => '1',
                    ])
                );
        });

        $schedule = ScheduleCleaning::where('status', ScheduleCleaningStatusEnum::Done())
            ->first();

        /** @var \App\Models\ScheduleEmployee|null */
        $scheduleEmployee = $schedule->scheduleEmployees()->first();
        $scheduleEmployee->start_at = $scheduleEmployee->start_at->setHour(8);
        $scheduleEmployee->end_at = $scheduleEmployee->start_at->copy()->addHours(1);
        $scheduleEmployee->save();

        WorkHour::where('user_id', $scheduleEmployee->user_id)->delete();

        $workHourService = new WorkHourService();
        $workHourService->update($mockFortnoxEmployeeService, $scheduleEmployee);

        $this->assertDatabaseHas('work_hours', [
            'user_id' => $scheduleEmployee->user_id,
            'fortnox_attendance_id' => '1',
            'date' => $scheduleEmployee->start_at->setTimezone('Europe/Stockholm')->format('Y-m-d'),
            'start_time' => $scheduleEmployee->start_at->setTimezone('Europe/Stockholm')->format('H:i:s'),
            'end_time' => $scheduleEmployee->end_at->setTimezone('Europe/Stockholm')->format('H:i:s'),
        ]);
    }

    public function testCanUpdateWorkHour(): void
    {
        $mockFortnoxEmployeeService = $this->mock(FortnoxEmployeeService::class, function (MockInterface $mock) {
            $mock->shouldReceive('createAttendanceTransaction')
                ->atMost()
                ->once()
                ->andReturn(
                    AttendanceTransactionDTO::from([
                        'id' => '1',
                    ])
                );
            $mock->shouldReceive('updateAttendanceTransaction')
                ->atMost()
                ->once()
                ->andReturn(
                    AttendanceTransactionDTO::from([
                        'id' => '1',
                    ])
                );
        });

        $schedule = ScheduleCleaning::where('status', ScheduleCleaningStatusEnum::Done())
            ->first();

        /** @var \App\Models\ScheduleEmployee|null */
        $scheduleEmployee = $schedule->scheduleEmployees()->first();
        $scheduleEmployee->start_at = $scheduleEmployee->start_at->setHour(8);
        $scheduleEmployee->end_at = $scheduleEmployee->start_at->copy()->addHours(1);
        $scheduleEmployee->save();

        ScheduleEmployee::where('work_hour_id', $scheduleEmployee->work_hour_id)
            ->update(['work_hour_id' => null]);
        ScheduleEmployee::where('user_id', $scheduleEmployee->user_id)
            ->whereNot('id', $scheduleEmployee->id)
            ->delete();
        WorkHour::where('user_id', $scheduleEmployee->user_id)->delete();

        $workHourService = new WorkHourService();
        $workHourService->update($mockFortnoxEmployeeService, $scheduleEmployee);

        $this->assertDatabaseHas('work_hours', [
            'user_id' => $scheduleEmployee->user_id,
            'fortnox_attendance_id' => '1',
            'date' => $scheduleEmployee->start_at->setTimezone('Europe/Stockholm')->format('Y-m-d'),
            'start_time' => $scheduleEmployee->start_at->setTimezone('Europe/Stockholm')->format('H:i:s'),
            'end_time' => $scheduleEmployee->end_at->setTimezone('Europe/Stockholm')->format('H:i:s'),
        ]);

        $scheduleEmployee->update(['end_at' => $scheduleEmployee->end_at->addMinutes(15)]);
        $workHourService->update($mockFortnoxEmployeeService, $scheduleEmployee);

        $this->assertDatabaseHas('work_hours', [
            'user_id' => $scheduleEmployee->user_id,
            'fortnox_attendance_id' => '1',
            'date' => $scheduleEmployee->start_at->setTimezone('Europe/Stockholm')->format('Y-m-d'),
            'start_time' => $scheduleEmployee->start_at->setTimezone('Europe/Stockholm')->format('H:i:s'),
            'end_time' => $scheduleEmployee->end_at->setTimezone('Europe/Stockholm')->format('H:i:s'),
        ]);
    }

    public function testCanSplitWorkHourWhenEndAtIsAfterMidnight(): void
    {
        $mockFortnoxEmployeeService = $this->mock(FortnoxEmployeeService::class, function (MockInterface $mock) {
            $mock->shouldReceive('createAttendanceTransaction')
                ->twice()
                ->andReturn(
                    AttendanceTransactionDTO::from([
                        'id' => '1',
                    ]),
                    AttendanceTransactionDTO::from([
                        'id' => '2',
                    ])
                );
        });

        $schedule = ScheduleCleaning::where('status', ScheduleCleaningStatusEnum::Done())
            ->first();

        /** @var \App\Models\ScheduleEmployee|null */
        $scheduleEmployee = $schedule->scheduleEmployees()->first();
        $scheduleEmployee->start_at = $scheduleEmployee->start_at
            ->copy()
            ->setTimezone('Europe/Stockholm')
            ->setHour(23)
            ->utc();
        $scheduleEmployee->end_at = $scheduleEmployee->start_at->copy()->addHours(2);
        $scheduleEmployee->save();

        ScheduleEmployee::where('work_hour_id', $scheduleEmployee->work_hour_id)
            ->update(['work_hour_id' => null]);
        ScheduleEmployee::where('user_id', $scheduleEmployee->user_id)
            ->whereNot('id', $scheduleEmployee->id)
            ->delete();
        WorkHour::where('user_id', $scheduleEmployee->user_id)->delete();

        $workHourService = new WorkHourService();
        $workHourService->update($mockFortnoxEmployeeService, $scheduleEmployee);

        $this->assertDatabaseHas('work_hours', [
            'user_id' => $scheduleEmployee->user_id,
            'fortnox_attendance_id' => '1',
            'date' => $scheduleEmployee->start_at->setTimezone('Europe/Stockholm')->format('Y-m-d'),
            'start_time' => $scheduleEmployee->start_at->setTimezone('Europe/Stockholm')->format('H:i:s'),
            'end_time' => $scheduleEmployee->start_at
                ->copy()
                ->setTimezone('Europe/Stockholm')
                ->endOfDay()
                ->format('H:i:s'),
        ]);

        $this->assertDatabaseHas('work_hours', [
            'user_id' => $scheduleEmployee->user_id,
            'fortnox_attendance_id' => '2',
            'date' => $scheduleEmployee->end_at->setTimezone('Europe/Stockholm')->format('Y-m-d'),
            'start_time' => $scheduleEmployee->end_at
                ->copy()
                ->setTimezone('Europe/Stockholm')
                ->startOfDay()
                ->format('H:i:s'),
            'end_time' => $scheduleEmployee->end_at->setTimezone('Europe/Stockholm')->format('H:i:s'),
        ]);
    }
}
