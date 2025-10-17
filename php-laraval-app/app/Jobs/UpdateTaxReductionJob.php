<?php

namespace App\Jobs;

use App\DTOs\Fortnox\Invoice\InvoiceDTO;
use App\Models\Invoice;
use App\Services\Fortnox\FortnoxCustomerService;
use DB;
use Str;

class UpdateTaxReductionJob extends BaseJob
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
        protected ?InvoiceDTO $invoiceResponse = null,
    ) {
        $this->queue = 'default';
    }

    /**
     * Execute the job.
     */
    public function handle(
        FortnoxCustomerService $service,
    ): void {
        $taxReductions = $service->getTaxReductions(
            filter: 'invoices',
            referenceNumber: $this->invoice->fortnox_invoice_id,
        );

        if ($taxReductions->count() > 0) {
            // delete tax reduction
            foreach ($taxReductions as $taxReduction) {
                $service->deleteTaxReduction($taxReduction->id);
            }

            DB::transaction(function () {
                $this->invoice->update([
                    'fortnox_tax_reduction_id' => null,
                ]);

                // delete all meta data
                $keys = $this->invoice->pluckMeta()->keys();
                $keysToDelete = $keys->filter(fn ($key) => Str::startsWith($key, 'tax_reduction_co_applicant_id_'));
                $this->invoice->deleteMeta($keysToDelete->toArray());
            });
        }

        // create new tax reduction
        CreateTaxReductionJob::dispatchSync(
            $this->invoice,
            $this->invoiceResponse,
        );
    }
}
