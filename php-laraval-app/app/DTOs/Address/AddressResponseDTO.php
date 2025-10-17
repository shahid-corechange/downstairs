<?php

namespace App\DTOs\Address;

use App\DTOs\BaseData;
use App\Models\Address;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Lazy;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapInputName(SnakeCaseMapper::class)]
class AddressResponseDTO extends BaseData
{
    public function __construct(
        public Lazy|null|int $id,
        public Lazy|null|int $cityId,
        public Lazy|null|string $address,
        public Lazy|null|string $address2,
        public Lazy|null|string $area,
        public Lazy|null|string $postalCode,
        public Lazy|null|string $fullAddress,
        public Lazy|null|string $accuracy,
        public Lazy|null|string $latitude,
        public Lazy|null|string $longitude,
        public Lazy|null|string $createdAt,
        public Lazy|null|string $updatedAt,
        public Lazy|null|string $deletedAt,
        public Lazy|null|CityResponseDTO $city,
    ) {
    }

    public static function fromModel(Address $address): self
    {
        return new self(
            Lazy::create(fn () => $address->id)->defaultIncluded(),
            Lazy::create(fn () => $address->city_id)->defaultIncluded(),
            Lazy::create(fn () => $address->address)->defaultIncluded(),
            Lazy::create(fn () => $address->address_2)->defaultIncluded(),
            Lazy::create(fn () => $address->area)->defaultIncluded(),
            Lazy::create(fn () => $address->postal_code)->defaultIncluded(),
            Lazy::create(fn () => $address->full_address)->defaultIncluded(),
            Lazy::create(fn () => $address->accuracy)->defaultIncluded(),
            Lazy::create(fn () => $address->latitude)->defaultIncluded(),
            Lazy::create(fn () => $address->longitude)->defaultIncluded(),
            Lazy::create(fn () => $address->created_at)->defaultIncluded(),
            Lazy::create(fn () => $address->updated_at)->defaultIncluded(),
            Lazy::create(fn () => $address->deleted_at)->defaultIncluded(),
            Lazy::create(fn () => CityResponseDTO::from($address->city)),
        );
    }
}
