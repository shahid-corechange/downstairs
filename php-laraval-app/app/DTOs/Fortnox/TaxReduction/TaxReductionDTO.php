<?php

namespace App\DTOs\Fortnox\TaxReduction;

use App\DTOs\BaseData;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Mappers\StudlyCaseMapper;

#[MapInputName(StudlyCaseMapper::class)]
class TaxReductionDTO extends BaseData
{
    public function __construct(
        public ?float $approved_amount,
        public ?float $asked_amount,
        public ?float $billed_amount,
        public ?string $customer_name,
        public ?int $id,
        public ?string $property_designation,
        public ?string $reference_document_type,
        public ?string $reference_number,
        public ?bool $request_sent,
        public ?string $residence_association_organisation_number,
        public ?string $social_security_number,
        public ?int $voucher_number,
        public ?string $voucher_series,
        public ?int $voucher_year,
    ) {
    }
}
