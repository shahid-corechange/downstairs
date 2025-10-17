<?php

namespace App\DTOs\Fortnox\Invoice;

use App\DTOs\BaseData;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\Mappers\StudlyCaseMapper;
use Spatie\LaravelData\Optional;

#[MapInputName(StudlyCaseMapper::class)]
class InvoiceDTO extends BaseData
{
    public function __construct(
        public null|Optional|float $balance,
        public null|Optional|float $basis_tax_reduction,
        public null|Optional|bool $booked,
        public null|Optional|bool $cancelled,
        public null|Optional|string $cost_center,
        public null|Optional|string $currency,
        public null|Optional|float $currency_rate,
        public null|Optional|float $currency_unit,
        public null|Optional|string $customer_name,
        public null|Optional|string $customer_number,
        public null|Optional|string $document_number,
        public null|Optional|string $due_date,
        public null|Optional|string $external_invoice_reference1,
        public null|Optional|string $external_invoice_reference2,
        public null|Optional|string $invoice_date,
        public null|Optional|string $invoice_type,
        public null|Optional|bool $nox_finans,
        public null|Optional|string $ocr,
        public null|Optional|string $organisation_number,
        public null|Optional|int $voucher_number,
        public null|Optional|string $voucher_series,
        public null|Optional|int $voucher_year,
        public null|Optional|string $way_of_delivery,
        public null|Optional|string $terms_of_payment,
        public null|Optional|string $project,
        public null|Optional|bool $sent,
        public null|Optional|float $total,
        public null|Optional|string $final_pay_date,
        #[DataCollectionOf(InvoiceRowDTO::class)]
        public null|Optional|DataCollection $invoice_rows,
    ) {
    }
}
