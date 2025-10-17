<?php

namespace App\DTOs\Fortnox\Customer;

use App\DTOs\BaseData;
use Spatie\LaravelData\Attributes\MapOutputName;
use Spatie\LaravelData\Mappers\StudlyCaseMapper;
use Spatie\LaravelData\Optional;

#[MapOutputName(StudlyCaseMapper::class)]
class CreateFortnoxCustomerRequestDTO extends BaseData
{
    public function __construct(
        public string|null|Optional $customer_number,
        public string $name,
        public string|null|Optional $organisation_number,
        public string|null|Optional $address1,
        public string|null|Optional $address2,
        public string|null|Optional $city,
        public string $currency,
        public string|null|Optional $country_code,
        public bool|Optional $active,
        public ?string $email,
        public ?string $phone1,
        public string $type,
        public string|null|Optional $zip_code,
        public FortnoxCustomerDeliveryTypesRequestDTO|Optional $default_delivery_types,
        public ?string $email_invoice,
    ) {
    }
}
