<?php

namespace App\DTOs\Fortnox\InvoicePayment;

use App\DTOs\BaseData;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Mappers\StudlyCaseMapper;
use Spatie\LaravelData\Optional;

#[MapInputName(StudlyCaseMapper::class)]
class InvoicePaymentDTO extends BaseData
{
    public function __construct(
        public null|Optional|float $amount,
        public null|Optional|float $amount_currency,
        public null|Optional|bool $booked,
        public null|Optional|string $currency,
        public null|Optional|float $currency_rate,
        public null|Optional|float $currency_unit,
        public null|Optional|string $external_invoice_reference1,
        public null|Optional|string $external_invoice_reference2,
        public null|Optional|string $invoice_customer_name,
        public null|Optional|string $invoice_customer_number,
        public null|Optional|string $invoice_due_date,
        public null|Optional|int $invoice_number,
        public null|Optional|string $invoice_ocr,
        public null|Optional|string $invoice_total,
        public null|Optional|string $mode_of_payment,
        public null|Optional|int $mode_of_payment_account,
        public null|Optional|string $number,
        public null|Optional|string $payment_date,
        public null|Optional|string $source,
        public null|Optional|int $voucher_number,
        public null|Optional|string $voucher_series,
        public null|Optional|int $voucher_year,
        public null|Optional|InvoicePaymentWriteOffDTO $write_offs,
    ) {
    }
}
