<?php

namespace App\DTOs\Fortnox\Customer;

use App\DTOs\BaseData;
use Spatie\LaravelData\Attributes\MapOutputName;
use Spatie\LaravelData\Mappers\StudlyCaseMapper;
use Spatie\LaravelData\Optional;

#[MapOutputName(StudlyCaseMapper::class)]
class UpdateFortnoxCustomerRequestDTO extends BaseData
{
    public function __construct(
        public string $name,
        public string|null|Optional $customer_number,
        public string|null|Optional $organisation_number,
        public string|null|Optional $type,
        public string|null|Optional $email,
        public string|null|Optional $phone1,
        public string|null|Optional $address1,
        public string|null|Optional $address2,
        public string|null|Optional $city,
        public string|null|Optional $country,
        public string|null|Optional $currency,
        public string|null|Optional $country_code,
        public string|null|Optional $zip_code,
        public string|null|Optional $email_invoice,
        public bool|null|Optional $active,
        public FortnoxCustomerDeliveryTypesRequestDTO|Optional $default_delivery_types,
    ) {
    }
}
