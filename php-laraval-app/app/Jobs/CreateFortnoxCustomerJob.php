<?php

namespace App\Jobs;

use App\Models\Customer;
use App\Services\Fortnox\FortnoxCustomerService;

class CreateFortnoxCustomerJob extends BaseJob
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
        protected Customer $customer,
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
            $fortnoxService->syncCustomer($this->customer);
        });
    }
}
