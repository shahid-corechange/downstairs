<?php

namespace App\DTOs\Address;

use App\DTOs\BaseData;
use App\Models\Country;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\Lazy;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapInputName(SnakeCaseMapper::class)]
class CountryResponseDTO extends BaseData
{
    public function __construct(
        public Lazy|null|int $id,
        public Lazy|null|string $code,
        public Lazy|null|string $name,
        public Lazy|null|string $currency,
        public Lazy|null|string $dialCode,
        #[DataCollectionOf(CityResponseDTO::class)]
        public Lazy|null|DataCollection $cities,
    ) {
    }

    public static function fromModel(Country $country): self
    {
        return new self(
            Lazy::create(fn () => $country->id)->defaultIncluded(),
            Lazy::create(fn () => $country->code)->defaultIncluded(),
            Lazy::create(fn () => $country->name)->defaultIncluded(),
            Lazy::create(fn () => $country->currency)->defaultIncluded(),
            Lazy::create(fn () => $country->dial_code)->defaultIncluded(),
            Lazy::create(fn () => CityResponseDTO::collection($country->cities)),
        );
    }
}
