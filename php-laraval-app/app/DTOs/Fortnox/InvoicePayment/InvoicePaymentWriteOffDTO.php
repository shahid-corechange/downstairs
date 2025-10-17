<?php

namespace App\DTOs\Fortnox\InvoicePayment;

use App\DTOs\BaseData;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Mappers\StudlyCaseMapper;
use Spatie\LaravelData\Optional;

#[MapInputName(StudlyCaseMapper::class)]
class InvoicePaymentWriteOffDTO extends BaseData
{
    public function __construct(
        public null|Optional|int $account_number,
        public null|Optional|float $amount,
        public null|Optional|string $cost_center,
        public null|Optional|string $currency,
        public null|Optional|string $description,
        public null|Optional|string $project,
        public null|Optional|string $transaction_information,
    ) {
    }
}
