<?php

namespace App\DTOs\Fortnox\Customer;

use App\DTOs\BaseData;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Mappers\StudlyCaseMapper;

#[MapInputName(StudlyCaseMapper::class)]
class CustomerDTO extends BaseData
{
    public function __construct(
        public ?string $customer_number,
        public ?string $name,
        public ?string $organisation_number,
        public ?string $address1,
        public ?string $address2,
        public ?string $city,
        public ?string $country,
        public ?string $currency,
        public ?string $country_code,
        public ?bool $active,
        public ?string $email,
        public ?string $phone1,
        public ?string $type,
        public ?string $zip_code,
        public ?FortnoxCustomerDeliveryTypesRequestDTO $default_delivery_types,
        public ?string $email_invoice,
    ) {
    }
}
