<?php

namespace App\DTOs\Fortnox\Customer;

use App\DTOs\BaseData;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Mappers\StudlyCaseMapper;
use Spatie\LaravelData\Optional;

#[MapInputName(StudlyCaseMapper::class)]
class FortnoxCustomerDeliveryTypesResponseDTO extends BaseData
{
    public function __construct(
        public string|null|Optional $invoice,
        public string|null|Optional $order,
        public string|null|Optional $offer,
    ) {
    }
}
