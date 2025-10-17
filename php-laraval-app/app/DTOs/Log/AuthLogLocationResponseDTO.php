<?php

namespace App\DTOs\Log;

use App\DTOs\BaseData;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Lazy;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapInputName(SnakeCaseMapper::class)]
class AuthLogLocationResponseDTO extends BaseData
{
    public function __construct(
        public Lazy|null|int $id,
        public Lazy|null|string $ip,
        public Lazy|null|int $lat,
        public Lazy|null|int $lon,
        public Lazy|null|string $city,
        public Lazy|null|string $state,
        public Lazy|null|bool $cached,
        public Lazy|null|string $country,
        public Lazy|null|string $isoCode,
        public Lazy|null|string $timezone,
        public Lazy|null|string $continent,
        public Lazy|null|string $stateName,
        public Lazy|null|string $postalCode,
    ) {
    }
}
