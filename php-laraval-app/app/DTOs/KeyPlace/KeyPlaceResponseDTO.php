<?php

namespace App\DTOs\KeyPlace;

use App\DTOs\BaseData;
use App\DTOs\Property\PropertyResponseDTO;
use App\Models\KeyPlace;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Lazy;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapInputName(SnakeCaseMapper::class)]
class KeyPlaceResponseDTO extends BaseData
{
    public function __construct(
        public Lazy|null|int $id,
        public Lazy|null|int $propertyId,
        public Lazy|null|PropertyResponseDTO $property,
    ) {
    }

    public static function fromModel(KeyPlace $keyPlace): self
    {
        return new self(
            Lazy::create(fn () => $keyPlace->id)->defaultIncluded(),
            Lazy::create(fn () => $keyPlace->property_id)->defaultIncluded(),
            Lazy::create(fn () => $keyPlace->property_id ? PropertyResponseDTO::from($keyPlace->property) : null),
        );
    }
}
