<?php

namespace App\Jobs;

use App\Enums\Invoice\InvoiceStatusEnum;
use App\Enums\Order\OrderStatusEnum;
use App\Models\Invoice;
use App\Services\Fortnox\FortnoxCustomerService;
use DB;

class SentInvoiceJob extends BaseJob
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
    public function handle(
        FortnoxCustomerService $service,
    ): void {
        $this->handleWrapper(function () use ($service) {
            $sendRes = $service->sendInvoiceAsEmail($this->invoice->fortnox_invoice_id);

            if ($sendRes) {
                DB::transaction(function () {
                    $this->invoice->update([
                        'status' => InvoiceStatusEnum::Sent(),
                    ]);

                    $this->invoice->orders()->update([
                        'status' => OrderStatusEnum::Done(),
                    ]);
                });
            }
        });
    }
}
