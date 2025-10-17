<?php

namespace App\Jobs;

use App\Models\FixedPrice;
use App\Services\InvoiceSummationService;
use App\Services\OrderFixedPriceService;

class CreateOrderFixedPriceJob extends BaseJob
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
        protected FixedPrice $fixedPrice,
        protected bool $isPerOrderChanged,
    ) {
        $this->queue = 'default';
    }

    /**
     * Execute the job.
     */
    public function handle(
        OrderFixedPriceService $fixedPriceService,
        InvoiceSummationService $invoiceSummationService,
    ): void {
        $this->handleWrapper(function () use ($fixedPriceService, $invoiceSummationService) {
            $fixedPriceService->applyToSubscriptionOrders(
                $this->fixedPrice,
                $this->isPerOrderChanged,
                $invoiceSummationService,
            );
        });
    }
}
