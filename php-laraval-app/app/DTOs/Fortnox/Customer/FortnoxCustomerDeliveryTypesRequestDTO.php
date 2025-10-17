<?php

namespace App\DTOs\Fortnox\Customer;

use App\DTOs\BaseData;
use Spatie\LaravelData\Attributes\MapOutputName;
use Spatie\LaravelData\Mappers\StudlyCaseMapper;
use Spatie\LaravelData\Optional;

#[MapOutputName(StudlyCaseMapper::class)]
class FortnoxCustomerDeliveryTypesRequestDTO extends BaseData
{
    public function __construct(
        public string|null|Optional $invoice,
        public string|null|Optional $order,
        public string|null|Optional $offer,
    ) {
    }
}
