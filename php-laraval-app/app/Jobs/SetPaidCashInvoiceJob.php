<?php

namespace App\Jobs;

use App\DTOs\Fortnox\Invoice\InvoiceDTO;
use App\DTOs\Fortnox\InvoicePayment\CreateInvoicePaymentRequestDTO;
use App\DTOs\Fortnox\InvoicePayment\UpdateInvoicePaymentRequestDTO;
use App\Enums\Invoice\InvoiceStatusEnum;
use App\Enums\Order\OrderStatusEnum;
use App\Exceptions\OperationFailedException;
use App\Models\Invoice;
use App\Services\Fortnox\FortnoxCustomerService;
use App\Services\TaxReductionService;
use DB;

class SetPaidCashInvoiceJob extends BaseJob
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
        TaxReductionService $taxReductionService,
    ): void {
        $invoiceResponse = $this->invoiceResponse ?? $service->getInvoice($this->invoice->fortnox_invoice_id);

        if ($invoiceResponse) {
            try {
                $service->bookkeepInvoice($this->invoice->fortnox_invoice_id);
                $service->setInvoiceAsSent($this->invoice->fortnox_invoice_id);

                /**
                 * Create invoice payment and bookkeep it
                 */
                $response = $service->createInvoicePayment(CreateInvoicePaymentRequestDTO::from([
                    'amount' => $invoiceResponse->total,
                    'invoice_number' => $invoiceResponse->document_number,
                ]));
                $service->bookkeepInvoicePayment($response->number, UpdateInvoicePaymentRequestDTO::from([
                    'invoice_number' => $invoiceResponse->document_number,
                ]));

                /**
                 * Get tax reduction from Fortnox and save to invoice table for record.
                 */
                $taxReduction = $taxReductionService->get($service, $this->invoice);

                DB::transaction(function () use (
                    $taxReduction,
                ) {
                    $this->invoice->update([
                        'status' => InvoiceStatusEnum::Paid(),
                        'fortnox_tax_reduction_id' => $taxReduction['tax_reduction'],
                    ]);
                    $this->invoice->orders()->update([
                        'status' => OrderStatusEnum::Done(),
                    ]);

                    if ($this->invoice->user->rutCoApplicants->isNotEmpty()) {
                        $id = $this->invoice->user->rutCoApplicants->first()->id;

                        $this->invoice->saveMeta([
                            "tax_reduction_co_applicant_id_{$id}" => $taxReduction['co_applicant_tax_reduction'],
                        ]);
                    }
                });
            } catch (OperationFailedException $e) {
                throw $e;
            }
        }
    }
}
