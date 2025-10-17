<?php

namespace App\Jobs;

use App\Models\ScheduleEmployee;
use App\Services\Fortnox\FortnoxEmployeeService;
use App\Services\WorkHourService;

class SentWorkingHoursJob extends BaseJob
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
        protected ScheduleEmployee $schedule,
    ) {
        $this->queue = 'default';
    }

    /**
     * Execute the job.
     */
    public function handle(
        FortnoxEmployeeService $fortnoxService,
        WorkHourService $workHourService,
    ): void {
        $this->handleWrapper(function () use ($fortnoxService, $workHourService) {
            $workHourService->update($fortnoxService, $this->schedule);
        });
    }
}
