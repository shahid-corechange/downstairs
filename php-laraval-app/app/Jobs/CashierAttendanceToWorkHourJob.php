<?php

namespace App\Jobs;

use App\Models\CashierAttendance;
use App\Services\WorkHourService;

class CashierAttendanceToWorkHourJob extends BaseJob
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
        protected CashierAttendance $attendance,
    ) {
        $this->queue = 'default';
    }

    /**
     * Execute the job.
     */
    public function handle(
        WorkHourService $workHourService,
    ): void {
        $this->handleWrapper(function () use ($workHourService) {
            $workHourService->updateFromCashierAttendance($this->attendance);
        });
    }
}
