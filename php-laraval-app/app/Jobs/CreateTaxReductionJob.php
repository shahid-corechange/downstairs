<?php

namespace App\Jobs;

use App\DTOs\Fortnox\Invoice\InvoiceDTO;
use App\DTOs\Fortnox\TaxReduction\CreateTaxReductionRequestDTO;
use App\Enums\Invoice\InvoiceTypeEnum;
use App\Exceptions\OperationFailedException;
use App\Helpers\Validation\SwedishSocialSecurityNumberValidation;
use App\Models\Invoice;
use App\Models\RutCoApplicant;
use App\Services\Fortnox\FortnoxCustomerService;
use DB;
use Illuminate\Http\Response;

class CreateTaxReductionJob extends BaseJob
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
        $invoiceResponse = $this->invoiceResponse ?? $service->getInvoice($this->invoice->fortnox_invoice_id);

        if ($invoiceResponse) {
            $percentage = $this->invoice->type === InvoiceTypeEnum::Laundry() ? 0.25 : 0.5;

            $rutCoApplicants = $this->invoice->user->rutCoApplicants->filter(function (RutCoApplicant $rutCoApplicant) {
                return $rutCoApplicant->is_enabled
                    && ! $rutCoApplicant->is_paused
                    && SwedishSocialSecurityNumberValidation::validate($rutCoApplicant->identity_number);
            });

            // for devide the tax reduction amount between the co-applicants
            $totalRutApplicant = $rutCoApplicants->count() || 1;
            $baseTaxReduction = $invoiceResponse->basis_tax_reduction * $percentage;
            // if invoice type is laundry, we don't need to floor the basis tax reduction
            $baseAmount = $this->invoice->type === InvoiceTypeEnum::Laundry() ?
                $baseTaxReduction : floor($baseTaxReduction);
            // Amount to input in tax reduction
            $askedAmount = $baseAmount / $totalRutApplicant;

            $meta = [];
            $taxReductionId = null;

            // Create tax reduction for co-applicants
            if ($rutCoApplicants->isNotEmpty()) {
                foreach ($rutCoApplicants as $rutCoApplicant) {
                    while (true) {
                        try {
                            $taxReductionResponse = $service->createTaxReduction(
                                CreateTaxReductionRequestDTO::from([
                                    'asked_amount' => $askedAmount,
                                    'customer_name' => $rutCoApplicant->name,
                                    'reference_document_type' => 'INVOICE',
                                    'reference_number' => $invoiceResponse->document_number,
                                    'social_security_number' => $rutCoApplicant->identity_number,
                                ])
                            );

                            /**
                             * Save tax reduction rut co-applicant id to meta
                             * Add rut co applicant id in key to make it easy to debug or get value
                             */
                            if ($taxReductionResponse) {
                                $key = "tax_reduction_co_applicant_id_{$rutCoApplicant->id}";
                                $meta[$key] = $taxReductionResponse->id;
                                $taxReductionId = $taxReductionId ?? $taxReductionResponse->id;
                            }

                            break;
                        } catch (OperationFailedException $e) {
                            if ($e->getCode() === Response::HTTP_TOO_MANY_REQUESTS) {
                                sleep(5);

                                continue;
                            }

                            throw $e;
                        }
                    }
                }

                DB::transaction(function () use ($taxReductionId, $meta) {
                    $this->invoice->update([
                        'fortnox_tax_reduction_id' => $taxReductionId,
                    ]);

                    if ($meta) {
                        $this->invoice->deleteMeta(array_keys($meta));
                        $this->invoice->saveMeta($meta);
                    }
                });
            }
        }
    }
}
