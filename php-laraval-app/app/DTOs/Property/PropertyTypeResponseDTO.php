<?php

namespace App\DTOs\Property;

use App\DTOs\BaseData;
use App\Models\PropertyType;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Lazy;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapInputName(SnakeCaseMapper::class)]
class PropertyTypeResponseDTO extends BaseData
{
    public function __construct(
        public Lazy|null|int $id,
        public Lazy|null|string $name,
        public Lazy|null|string $createdAt,
        public Lazy|null|string $updatedAt,
        public Lazy|null|string $deletedAt,
    ) {
    }

    public static function fromModel(PropertyType $propertyType): self
    {
        return new self(
            Lazy::create(fn () => $propertyType->id)->defaultIncluded(),
            Lazy::create(fn () => $propertyType->name)->defaultIncluded(),
            Lazy::create(fn () => $propertyType->created_at)->defaultIncluded(),
            Lazy::create(fn () => $propertyType->updated_at)->defaultIncluded(),
            Lazy::create(fn () => $propertyType->deleted_at)->defaultIncluded(),
        );
    }
}
