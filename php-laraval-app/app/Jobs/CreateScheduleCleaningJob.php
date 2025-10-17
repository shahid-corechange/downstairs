<?php

namespace App\Jobs;

use App\DTOs\Subscription\SubscriptionScheduleDTO;
use App\Services\Schedule\ScheduleCleaningService;

class CreateScheduleCleaningJob extends BaseJob
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
        protected SubscriptionScheduleDTO $data
    ) {
        $this->queue = 'schedules';
    }

    /**
     * Execute the job.
     */
    public function handle(
        ScheduleCleaningService $service,
    ): void {
        $this->handleWrapper(function () use ($service) {
            $service->store($this->data);
        });
    }
}
