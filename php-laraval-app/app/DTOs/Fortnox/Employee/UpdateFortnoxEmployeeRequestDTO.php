<?php

namespace App\DTOs\Fortnox\Employee;

use App\DTOs\BaseData;
use Spatie\LaravelData\Attributes\MapOutputName;
use Spatie\LaravelData\Mappers\StudlyCaseMapper;
use Spatie\LaravelData\Optional;

#[MapOutputName(StudlyCaseMapper::class)]
class UpdateFortnoxEmployeeRequestDTO extends BaseData
{
    public function __construct(
        public string|null|Optional $personal_identity_number,
        public string $first_name,
        public string $last_name,
        public string $address1,
        public string|null|Optional $address2,
        public string $post_code,
        public string $city,
        public string $country,
        public string $phone1,
        public string $email,
    ) {
    }
}
