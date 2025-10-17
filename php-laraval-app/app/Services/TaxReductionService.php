<?php

namespace App\Services;

use App\DTOs\Fortnox\TaxReduction\TaxReductionDTO;
use App\Models\Invoice;
use App\Services\Fortnox\FortnoxCustomerService;

class TaxReductionService
{
    /**
     * Get tax reduction from Fortnox.
     */
    public function get(FortnoxCustomerService $fortnoxService, Invoice $invoice): array
    {
        $response = $fortnoxService->getTaxReductions('invoices', $invoice->fortnox_invoice_id);

        if ($response) {
            /** @var TaxReductionDTO $taxReduction */
            $taxReduction = $response->first(function (TaxReductionDTO $item) use ($invoice) {
                return str_replace('-', '', $item->social_security_number) === $invoice->customer->identity_number;
            });

            /** @var TaxReductionDTO $coApllicantTaxReduction */
            $coApllicantTaxReduction = $response->first(function (TaxReductionDTO $item) use ($invoice) {
                return str_replace('-', '', $item->social_security_number) !== $invoice->customer->identity_number;
            });

            return [
                'tax_reduction' => $taxReduction ? $taxReduction->id : null,
                'co_applicant_tax_reduction' => $coApllicantTaxReduction ? $coApllicantTaxReduction->id : null,
            ];
        }

        return [
            'tax_reduction' => null,
            'co_applicant_tax_reduction' => null,
        ];
    }
}
