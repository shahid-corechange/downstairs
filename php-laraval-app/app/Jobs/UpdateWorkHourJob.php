<?php

namespace App\Jobs;

use App\DTOs\Fortnox\AttendanceTransaction\AttendanceTransactionRequestDTO;
use App\Models\WorkHour;
use App\Services\Fortnox\FortnoxEmployeeService;

class UpdateWorkHourJob extends BaseJob
{
    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 5;

    /**
     * Create a new job instance.
     */
    public function __construct(
        protected WorkHour $workHour,
    ) {
        $this->queue = 'default';
    }

    /**
     * Execute the job.
     */
    public function handle(
        FortnoxEmployeeService $fortnoxService,
    ): void {
        $this->handleWrapper(function () use ($fortnoxService) {
            $startAt = $this->workHour->date->setTimeFromTimeString($this->workHour->start_time);
            $endAt = $this->workHour->date->setTimeFromTimeString($this->workHour->end_time);

            $workHour = ceil($startAt->diffInMinutes($endAt) / 15) / 4;
            $fortnoxEmployeeId = $this->workHour->user->employee->fortnox_id;

            $fortnoxService->updateAttendanceTransaction(
                $this->workHour->fortnox_attendance_id,
                AttendanceTransactionRequestDTO::from([
                    'employee_id' => $fortnoxEmployeeId,
                    'cause_code' => 'TID',
                    'date' => $startAt->format('Y-m-d'),
                    'hours' => $workHour + $this->workHour->time_adjustment_hours,
                ])
            );
        });
    }
}
