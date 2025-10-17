<?php

namespace App\DTOs\LaundryPreference;

use App\DTOs\BaseData;
use App\Models\LaundryPreference;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Lazy;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapInputName(SnakeCaseMapper::class)]
class LaundryPreferenceResponseDTO extends BaseData
{
    public function __construct(
        public Lazy|null|int $id,
        public Lazy|null|string $name,
        public Lazy|null|string $description,
        public Lazy|null|float $price,
        public Lazy|null|float $percentage,
        public Lazy|null|int $vatGroup,
        public Lazy|null|float $priceWithVat,
        public Lazy|null|int $hours,
        public Lazy|null|bool $includeHolidays,
        public Lazy|null|string $createdAt,
        public Lazy|null|string $updatedAt,
        public Lazy|null|string $deletedAt,
    ) {
    }

    public static function fromModel(LaundryPreference $laundryPreference): self
    {

        return new self(
            Lazy::create(fn () => $laundryPreference->id)->defaultIncluded(),
            Lazy::create(fn () => $laundryPreference->name)->defaultIncluded(),
            Lazy::create(fn () => $laundryPreference->description)->defaultIncluded(),
            Lazy::create(fn () => $laundryPreference->price)->defaultIncluded(),
            Lazy::create(fn () => $laundryPreference->percentage)->defaultIncluded(),
            Lazy::create(fn () => $laundryPreference->vat_group)->defaultIncluded(),
            Lazy::create(fn () => $laundryPreference->price_with_vat)->defaultIncluded(),
            Lazy::create(fn () => $laundryPreference->hours)->defaultIncluded(),
            Lazy::create(fn () => $laundryPreference->include_holidays)->defaultIncluded(),
            Lazy::create(fn () => $laundryPreference->created_at)->defaultIncluded(),
            Lazy::create(fn () => $laundryPreference->updated_at)->defaultIncluded(),
            Lazy::create(fn () => $laundryPreference->deleted_at)->defaultIncluded(),
        );
    }
}
