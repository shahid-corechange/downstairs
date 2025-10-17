<?php

namespace App\DTOs\Address;

use App\DTOs\BaseData;
use App\Transformers\StringTransformer;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\WithTransformer;
use Spatie\LaravelData\Mappers\CamelCaseMapper;
use Spatie\LaravelData\Optional;

#[MapInputName(CamelCaseMapper::class)]
class UpdateAddressRequestDTO extends BaseData
{
    public function __construct(
        public int|Optional $city_id,
        #[WithTransformer(StringTransformer::class)]
        public string|Optional $address,
        #[WithTransformer(StringTransformer::class)]
        public string|Optional $address_2,
        #[WithTransformer(StringTransformer::class)]
        public string|Optional $postal_code,
        public float|Optional $accuracy,
        public float|Optional $latitude,
        public float|Optional $longitude,
    ) {
    }

    public static function rules(): array
    {
        return [
            'city_id' => 'numeric|exists:cities,id',
            'address' => 'string',
            'address_2' => 'string',
            'postal_code' => 'string',
            'accuracy' => 'numeric',
            'latitude' => 'numeric',
            'longitude' => 'numeric',
        ];
    }
}
