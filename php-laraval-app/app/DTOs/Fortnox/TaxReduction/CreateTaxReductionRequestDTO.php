<?php

namespace App\DTOs\Fortnox\TaxReduction;

use App\DTOs\BaseData;
use Spatie\LaravelData\Attributes\MapOutputName;
use Spatie\LaravelData\Mappers\StudlyCaseMapper;
use Spatie\LaravelData\Optional;

#[MapOutputName(StudlyCaseMapper::class)]
class CreateTaxReductionRequestDTO extends BaseData
{
    public function __construct(
        public float|Optional $approved_amount,
        public float $asked_amount,
        public float|Optional $billed_amount,
        public string $customer_name,
        public int|Optional $id,
        public string|Optional $property_designation,
        public string $reference_document_type,
        public string $reference_number,
        public bool|Optional $request_sent,
        public string|Optional $residence_association_organisation_number,
        public string $social_security_number,
        public int|Optional $voucher_number,
        public string|Optional $voucher_series,
        public int|Optional $voucher_year,
    ) {
    }
}
