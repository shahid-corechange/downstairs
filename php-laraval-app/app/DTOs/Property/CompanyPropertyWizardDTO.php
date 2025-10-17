<?php

namespace App\DTOs\Property;

use App\DTOs\BaseData;
use App\Rules\MetaProperty;
use App\Rules\MetaRule;
use App\Transformers\StringTransformer;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\WithTransformer;
use Spatie\LaravelData\Mappers\CamelCaseMapper;
use Spatie\LaravelData\Optional;

#[MapInputName(CamelCaseMapper::class)]
class CompanyPropertyWizardDTO extends BaseData
{
    public function __construct(
        public int $user_id,
        public int $property_type_id,
        public float $square_meter,
        public KeyInformationRequestDTO|null|Optional $key_information,
        // Address
        public int $city_id,
        #[WithTransformer(StringTransformer::class)]
        public string $address,
        #[WithTransformer(StringTransformer::class)]
        public string|null|Optional $area,
        #[WithTransformer(StringTransformer::class)]
        public string $postal_code,
        public float|null|Optional $latitude,
        public float|null|Optional $longitude,
        // meta
        public array|Optional $meta,
    ) {
    }

    public static function rules(): array
    {
        return [
            'user_id' => 'required|numeric|exists:users,id',
            'property_type_id' => 'required|numeric|exists:property_types,id',
            'square_meter' => 'required|numeric',
            'key_information' => 'nullable|array',
            // Address
            'city_id' => 'required|numeric|exists:cities,id',
            'address' => 'required|string',
            'area' => 'nullable|string',
            'postal_code' => 'required|string',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            // meta
            'meta' => [new MetaRule()],
            'meta.*' => [new MetaProperty()],
        ];
    }
}
