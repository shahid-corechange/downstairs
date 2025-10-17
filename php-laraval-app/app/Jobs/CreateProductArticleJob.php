<?php

namespace App\Jobs;

use App\Models\Product;
use App\Services\Fortnox\FortnoxCustomerService;

class CreateProductArticleJob extends BaseJob
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
        protected Product $product,
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
            $fortnoxService->syncProduct($this->product);
        });
    }
}
