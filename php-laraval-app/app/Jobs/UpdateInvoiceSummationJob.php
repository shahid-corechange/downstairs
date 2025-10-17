<?php

namespace App\Jobs;

use App\Models\Invoice;
use App\Services\InvoiceSummationService;

class UpdateInvoiceSummationJob extends BaseJob
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
        protected Invoice $invoice,
    ) {
        $this->queue = 'default';
    }

    /**
     * Execute the job.
     */
    public function handle(InvoiceSummationService $invoiceSummationService): void
    {
        $this->handleWrapper(function () use ($invoiceSummationService) {
            $summation = $invoiceSummationService->getSummation($this->invoice);
            $this->invoice->update($summation);
        });
    }
}
