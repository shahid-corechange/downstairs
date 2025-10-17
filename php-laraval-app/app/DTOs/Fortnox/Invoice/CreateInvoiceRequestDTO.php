<?php

namespace App\DTOs\Fortnox\Invoice;

use App\DTOs\BaseData;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Attributes\MapOutputName;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\Mappers\StudlyCaseMapper;
use Spatie\LaravelData\Optional;

#[MapOutputName(StudlyCaseMapper::class)]
class CreateInvoiceRequestDTO extends BaseData
{
    public function __construct(
        public string|Optional $address2,
        public float|Optional $balance,
        public bool|Optional $booked,
        public bool|Optional $cancelled,
        public string|Optional $cost_center,
        public string|Optional $currency,
        public float|Optional $currency_rate,
        public float|Optional $currency_unit,
        public string|Optional $customer_name,
        public string|Optional $customer_number,
        public string|Optional $document_number,
        public string|Optional $due_date,
        public string|Optional $external_invoice_reference1,
        public string|Optional $external_invoice_reference2,
        public string|Optional $invoice_date,
        public string|Optional $invoice_type,
        public bool|Optional $nox_finans,
        public string|Optional $ocr,
        public int|Optional $voucher_number,
        public string|Optional $voucher_series,
        public int|Optional $voucher_year,
        public string|Optional $way_of_delivery,
        public string|Optional $terms_of_payment,
        public string|Optional $project,
        public bool|Optional $sent,
        public float|Optional $total,
        public string|Optional $final_pay_date,
        public string|Optional $tax_reduction_type,
        public string|Optional $payment_way,
        #[DataCollectionOf(CreateInvoiceRowDTO::class)]
        public ?DataCollection $invoice_rows,
        public string|Optional $your_reference,
        public string|Optional $remarks,
    ) {
    }
}
