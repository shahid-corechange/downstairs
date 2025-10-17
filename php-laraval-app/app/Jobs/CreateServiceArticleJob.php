<?php

namespace App\Jobs;

use App\Models\Service;
use App\Services\Fortnox\FortnoxCustomerService;

class CreateServiceArticleJob extends BaseJob
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
        protected Service $service,
    ) {
        $this->queue = 'default';
    }

    /**
     * Execute the job.
     */
    public function handle(
        FortnoxCustomerService $fortnoxService,
    ): void {
        $this->handleWrapper(function () use ($fortnoxService) {
            app()->setLocale('sv_SE');
            $fortnoxService->syncService($this->service);
        });
    }
}
