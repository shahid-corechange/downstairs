<?php

namespace App\Jobs;

use App\Enums\Invoice\InvoiceStatusEnum;
use App\Enums\Order\OrderStatusEnum;
use App\Models\FixedPrice;
use App\Models\Invoice;
use App\Services\InvoiceSummationService;
use Illuminate\Database\Eloquent\Builder;

class FixedPriceUpdateInvoiceSummationJob extends BaseJob
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
    ) {
        $this->queue = 'default';
    }

    /**
     * Execute the job.
     */
    public function handle(InvoiceSummationService $invoiceSummationService): void
    {
        $this->handleWrapper(function () use ($invoiceSummationService) {
            /** @var \Illuminate\Database\Eloquent\Collection<int,Invoice> */
            $invoices = Invoice::with(InvoiceSummationService::REQUIRED_FIELDS)
                ->where('status', InvoiceStatusEnum::Open())
                ->whereHas('orders', function (Builder $query) {
                    $query->where('status', OrderStatusEnum::Draft())
                        ->whereNotNull('order_fixed_price_id')
                        ->whereHas('fixedPrice', function (Builder $query) {
                            $query->where('fixed_price_id', $this->fixedPrice->id);
                        });
                })
                ->get();

            foreach ($invoices as $invoice) {
                $summation = $invoiceSummationService->getSummation($invoice);
                $invoice->update($summation);
            }
        });
    }
}
