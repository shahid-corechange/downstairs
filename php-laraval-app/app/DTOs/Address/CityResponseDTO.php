<?php

namespace App\DTOs\Address;

use App\DTOs\BaseData;
use App\Models\City;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\Lazy;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapInputName(SnakeCaseMapper::class)]
class CityResponseDTO extends BaseData
{
    public function __construct(
        public Lazy|null|int $id,
        public Lazy|null|string $name,
        public Lazy|null|int $countryId,
        #[DataCollectionOf(AddressResponseDTO::class)]
        public Lazy|null|DataCollection $addresses,
        public Lazy|null|CountryResponseDTO $country,
    ) {
    }

    public static function fromModel(City $city): self
    {
        return new self(
            Lazy::create(fn () => $city->id)->defaultIncluded(),
            Lazy::create(fn () => $city->name)->defaultIncluded(),
            Lazy::create(fn () => $city->country_id)->defaultIncluded(),
            Lazy::create(fn () => AddressResponseDTO::collection(
                $city->addresses()->withTrashed()->get()
            )),
            Lazy::create(fn () => CountryResponseDTO::from($city->country)),
        );
    }
}
