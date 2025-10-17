<?php

namespace App\Jobs;

use App\Models\Addon;
use App\Services\Fortnox\FortnoxCustomerService;

class CreateAddonArticleJob extends BaseJob
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
        protected Addon $addon,
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
            $fortnoxService->syncAddon($this->addon);
        });
    }
}
