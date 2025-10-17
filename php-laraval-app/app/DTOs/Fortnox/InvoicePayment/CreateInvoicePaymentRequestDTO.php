<?php

namespace App\DTOs\Fortnox\InvoicePayment;

use App\DTOs\BaseData;
use Spatie\LaravelData\Attributes\MapOutputName;
use Spatie\LaravelData\Mappers\StudlyCaseMapper;
use Spatie\LaravelData\Optional;

#[MapOutputName(StudlyCaseMapper::class)]
class CreateInvoicePaymentRequestDTO extends BaseData
{
    public function __construct(
        public float $amount,
        public Optional|float $amount_currency,
        public Optional|bool $booked,
        public Optional|string $currency,
        public Optional|float $currency_rate,
        public Optional|float $currency_unit,
        public Optional|string $external_invoice_reference1,
        public Optional|string $external_invoice_reference2,
        public Optional|string $invoice_customer_name,
        public Optional|string $invoice_customer_number,
        public Optional|string $invoice_due_date,
        public int $invoice_number,
        public Optional|string $invoice_ocr,
        public Optional|string $invoice_total,
        public Optional|string $mode_of_payment,
        public Optional|int $mode_of_payment_account,
        public Optional|string $number,
        public Optional|string $payment_date,
        public Optional|string $source,
        public Optional|int $voucher_number,
        public Optional|string $voucher_series,
        public Optional|int $voucher_year,
        public Optional|InvoicePaymentWriteOffDTO $write_offs,
    ) {
    }
}
