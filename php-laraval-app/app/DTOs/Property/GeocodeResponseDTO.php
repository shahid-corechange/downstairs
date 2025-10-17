<?php

namespace App\DTOs\Property;

use App\DTOs\BaseData;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapInputName(SnakeCaseMapper::class)]
class GeocodeResponseDTO extends BaseData
{
    public function __construct(
        public float $latitude,
        public float $longitude,
        public ?bool $partialMatch,
    ) {
    }
}
