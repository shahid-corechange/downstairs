<?php

namespace App\Services;

use App\DTOs\Fortnox\AttendanceTransaction\AttendanceTransactionRequestDTO;
use App\Enums\ScheduleEmployee\ScheduleEmployeeStatusEnum;
use App\Enums\WorkHour\WorkHourTypeEnum;
use App\Models\CashierAttendance;
use App\Models\ScheduleEmployee;
use App\Models\User;
use App\Models\WorkHour;
use App\Services\Fortnox\FortnoxEmployeeService;
use Carbon\Carbon;
use DB;

class WorkHourService
{
    public function __construct(
        private FortnoxEmployeeService $fortnoxService
    ) {
    }

    /**
     * Update work hours from schedule employee.
     */
    public function update(FortnoxEmployeeService $fortnoxService, ScheduleEmployee $scheduleEmployee): void
    {
        if (! $scheduleEmployee->start_at || ! $scheduleEmployee->end_at) {
            return;
        }

        $startAt = $scheduleEmployee->start_at->copy()->setTimezone('Europe/Stockholm');
        $endAt = $scheduleEmployee->end_at->copy()->setTimezone('Europe/Stockholm');

        if (! $startAt->isSameDay($endAt)) {
            $this->apply($fortnoxService, $scheduleEmployee, $startAt, $startAt->copy()->endOfDay());
            $this->apply($fortnoxService, $scheduleEmployee, $endAt->copy()->startOfDay(), $endAt);

            return;
        }

        $this->apply($fortnoxService, $scheduleEmployee, $startAt, $endAt);
    }

    public function updateFromCashierAttendance(CashierAttendance $attendance): void
    {
        if (! $attendance->check_in_at || ! $attendance->check_out_at) {
            return;
        }

        $this->applyFromCashierAttendance($attendance);
    }

    /**
     * Apply work hour update to the worker and send to Fortnox.
     */
    private function apply(
        FortnoxEmployeeService $fortnoxService,
        ScheduleEmployee $scheduleEmployee,
        Carbon $startAt,
        Carbon $endAt
    ): void {
        /**
         * Get user with trashed in case handle deviation
         * but the user is soft deleted.
         *
         * @var User $user
         */
        $user = $scheduleEmployee->user;
        $workerId = $scheduleEmployee->user_id;
        $fortnoxEmployeeId = $user->employee->fortnox_id;

        /** @var \App\Models\WorkHour|null */
        $workHour = WorkHour::where('user_id', $workerId)
            ->where('date', $startAt->format('Y-m-d'))
            ->first();

        if (! $workHour) {
            // Equalize the timezone.
            $startSchedule = $scheduleEmployee->start_at->copy()->setTimezone('Europe/Stockholm');
            /**
             * Check if the start schedule is between the start and end time.
             * It will be used to determine which work hours are appropriate if schedule employee over the day.
             */
            $isSuitableTimeAdjustment = $startSchedule->between($startAt, $endAt);
            $attendanceId = null;

            if ($fortnoxEmployeeId) {
                $workHours = self::calculate($startAt, $startAt->format('H:i:s'), $endAt->format('H:i:s'));
                $hours = $isSuitableTimeAdjustment ? $workHours + $scheduleEmployee->time_adjustment_hours : $workHours;

                try {
                    $response = $fortnoxService->createAttendanceTransaction(
                        AttendanceTransactionRequestDTO::from([
                            'employee_id' => $fortnoxEmployeeId,
                            'cause_code' => 'TID',
                            'date' => $startAt->format('Y-m-d'),
                            'hours' => $hours,
                        ])
                    );

                    if ($response) {
                        $attendanceId = $response->id;
                    }
                } catch (\Exception) {
                    // Do nothing in case Fortnox Id not match.
                }
            }

            DB::transaction(function () use (
                $scheduleEmployee,
                $workerId,
                $attendanceId,
                $startAt,
                $endAt,
                $isSuitableTimeAdjustment,
            ) {
                $workHour = WorkHour::create([
                    'user_id' => $workerId,
                    'fortnox_attendance_id' => $attendanceId,
                    'date' => $startAt->format('Y-m-d'),
                    'start_time' => $startAt->format('H:i:s'),
                    'end_time' => $endAt->format('H:i:s'),
                ]);

                if ($isSuitableTimeAdjustment) {
                    $scheduleEmployee->update([
                        'work_hour_id' => $workHour->id,
                    ]);
                }
            });

            return;
        }

        [$startTime, $endTime] = $this->getTimes($scheduleEmployee, $startAt->format('Y-m-d'));
        $startSchedule = $scheduleEmployee->start_at->copy()->setTimezone('Europe/Stockholm');

        /**
         * Get suitable work hour for the schedule employee.
         * Comparing base on the schedule employee is same day with work hour date.
         * This is to prevent wrong assign work hour id to over the day schedule employee.
         */
        $isSuitableWorkHour = $startSchedule->isSameDay($workHour->date);

        if ($fortnoxEmployeeId && $workHour->fortnox_attendance_id) {
            try {
                $workHours = self::calculate($startAt, $startTime, $endTime);

                /**
                 * If schedule employee work hour id is not exists and suitable work hour,
                 * must include schedule time adjustment, if not time adjustment will not be sent.
                 */
                $scheduleAdjustments = ! $scheduleEmployee->work_hour_id && $isSuitableWorkHour ?
                    $scheduleEmployee->time_adjustment_hours : 0;
                $adjustments = $workHour->time_adjustment_hours + $scheduleAdjustments;
                $hours = $workHours + $adjustments;

                $fortnoxService->updateAttendanceTransaction(
                    $workHour->fortnox_attendance_id,
                    AttendanceTransactionRequestDTO::from([
                        'employee_id' => $fortnoxEmployeeId,
                        'cause_code' => 'TID',
                        'date' => $startAt->format('Y-m-d'),
                        'hours' => $hours,
                    ])
                );
            } catch (\Exception) {
                // Do nothing in case Fortnox Id not match.
            }
        }

        DB::transaction(function () use ($workHour, $scheduleEmployee, $startTime, $endTime, $isSuitableWorkHour) {
            $workHour->update([
                'type' => WorkHourTypeEnum::Schedule(),
                'start_time' => $startTime,
                'end_time' => $endTime,
            ]);

            /**
             * Just update work hour id if not exists and suitable work hour
             * to prevent wrong calculation time adjustment in over the day schedule employee.
             */
            if (! $scheduleEmployee->work_hour_id && $isSuitableWorkHour) {
                $scheduleEmployee->update([
                    'work_hour_id' => $workHour->id,
                ]);
            }
        });
    }

    /**
     * Formula for calculate total hours.
     */
    public static function calculate(Carbon $date, string $startTime, string $endTime)
    {
        $startAt = $date->copy()->setTimeFromTimeString($startTime);
        $endAt = $date->copy()->setTimeFromTimeString($endTime);

        return ceil($startAt->diffInMinutes($endAt) / 15) / 4;
    }

    /**
     * Get earliest start from the schedules.
     */
    public static function getTimes(ScheduleEmployee $scheduleEmployee, string $date)
    {
        $startAt = Carbon::parse($date, 'Europe/Stockholm')->startOfDay()->utc();
        $endAt = Carbon::parse($date, 'Europe/Stockholm')->endOfDay()->utc();
        $schedules = ScheduleEmployee::where('user_id', $scheduleEmployee->user_id)
            ->whereBetween('start_at', [$startAt->copy()->subDay(), $endAt])
            ->whereBetween('end_at', [$startAt, $endAt->copy()->addDay()])
            ->where('status', ScheduleEmployeeStatusEnum::Done())
            ->orderBy('start_at')
            ->get();

        /** @var Carbon */
        $startAt = $schedules->min('start_at')->setTimezone('Europe/Stockholm');
        /** @var Carbon */
        $endAt = $schedules->max('end_at')->setTimezone('Europe/Stockholm');

        return [
            $startAt->isSameDay($date) ? $startAt->format('H:i:s') : $startAt->startOfDay()->format('H:i:s'),
            $endAt->isSameDay($date) ? $endAt->format('H:i:s') : $endAt->endOfDay()->format('H:i:s'),
        ];
    }

    private function applyFromCashierAttendance(CashierAttendance $attendance): void
    {
        /** @var User */
        $user = $attendance->user;
        $workerId = $attendance->user_id;
        $fortnoxEmployeeId = $user->employee->fortnox_id;
        $startAt = $attendance->check_in_at->copy()->setTimezone('Europe/Stockholm');
        $endAt = $attendance->check_out_at->copy()->setTimezone('Europe/Stockholm');

        /** @var \App\Models\WorkHour|null */
        $workHour = WorkHour::where('user_id', $workerId)
            ->where('date', $startAt->format('Y-m-d'))
            ->first();

        if (! $workHour) {
            $attendanceId = null;

            if ($fortnoxEmployeeId) {
                $hours = self::calculate($startAt, $startAt->format('H:i:s'), $endAt->format('H:i:s'));

                try {
                    $response = $this->fortnoxService->createAttendanceTransaction(
                        AttendanceTransactionRequestDTO::from([
                            'employee_id' => $fortnoxEmployeeId,
                            'cause_code' => 'TID',
                            'date' => $startAt->format('Y-m-d'),
                            'hours' => $hours,
                        ])
                    );

                    if ($response) {
                        $attendanceId = $response->id;
                    }
                } catch (\Exception) {
                    // Do nothing in case Fortnox Id not match.
                }

                DB::transaction(function () use (
                    $attendance,
                    $workerId,
                    $attendanceId,
                    $startAt,
                    $endAt,
                ) {
                    $workHour = WorkHour::create([
                        'user_id' => $workerId,
                        'fortnox_attendance_id' => $attendanceId,
                        'type' => WorkHourTypeEnum::Store(),
                        'date' => $startAt->format('Y-m-d'),
                        'start_time' => $startAt->format('H:i:s'),
                        'end_time' => $endAt->format('H:i:s'),
                    ]);

                    $attendance->update([
                        'work_hour_id' => $workHour->id,
                    ]);
                });

                return;
            }
        }

        [$startTime, $endTime] = $this->getAttendanceTimes($attendance, $startAt->format('Y-m-d'));

        if ($fortnoxEmployeeId && $workHour->fortnox_attendance_id) {
            try {
                $hours = self::calculate($startAt, $startTime, $endTime);

                $this->fortnoxService->updateAttendanceTransaction(
                    $workHour->fortnox_attendance_id,
                    AttendanceTransactionRequestDTO::from([
                        'employee_id' => $fortnoxEmployeeId,
                        'cause_code' => 'TID',
                        'date' => $startAt->format('Y-m-d'),
                        'hours' => $hours,
                    ])
                );
            } catch (\Exception) {
                // Do nothing in case Fortnox Id not match.
            }
        }

        DB::transaction(function () use ($workHour, $attendance, $startTime, $endTime) {
            $workHour->update([
                'start_time' => $startTime,
                'end_time' => $endTime,
            ]);

            /**
             * Just update work hour id if not exists and suitable work hour
             */
            if (! $attendance->work_hour_id) {
                $attendance->update([
                    'work_hour_id' => $workHour->id,
                ]);
            }
        });
    }

    /**
     * Get earliest start from the schedules.
     */
    private function getAttendanceTimes(CashierAttendance $attendance, string $date)
    {
        $startAt = Carbon::parse($date, 'Europe/Stockholm')->startOfDay()->utc();
        $endAt = Carbon::parse($date, 'Europe/Stockholm')->endOfDay()->utc();
        $attendances = CashierAttendance::where('user_id', $attendance->user_id)
            ->whereBetween('check_in_at', [$startAt->copy()->subDay(), $endAt])
            ->whereBetween('check_out_at', [$startAt, $endAt->copy()->addDay()])
            ->orderBy('check_in_at')
            ->get();

        /** @var Carbon */
        $startAt = $attendances->min('check_in_at')->setTimezone('Europe/Stockholm');
        /** @var Carbon */
        $endAt = $attendances->max('check_out_at')->setTimezone('Europe/Stockholm');

        return [
            $startAt->isSameDay($date) ? $startAt->format('H:i:s') : $startAt->startOfDay()->format('H:i:s'),
            $endAt->isSameDay($date) ? $endAt->format('H:i:s') : $endAt->endOfDay()->format('H:i:s'),
        ];
    }
}
