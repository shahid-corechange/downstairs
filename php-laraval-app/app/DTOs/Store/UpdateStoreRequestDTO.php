<?php

namespace App\DTOs\Store;

use App\DTOs\BaseData;
use App\Transformers\StringTransformer;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\WithTransformer;
use Spatie\LaravelData\Mappers\CamelCaseMapper;
use Spatie\LaravelData\Optional;

#[MapInputName(CamelCaseMapper::class)]
class UpdateStoreRequestDTO extends BaseData
{
    public function __construct(
        #[WithTransformer(StringTransformer::class)]
        public string|Optional $name,
        #[WithTransformer(StringTransformer::class)]
        public string|Optional $company_number,
        #[WithTransformer(StringTransformer::class)]
        public string|Optional $email,
        #[WithTransformer(StringTransformer::class)]
        public string|Optional $phone,
        public array|Optional $user_ids,
        // address
        public int $city_id,
        #[WithTransformer(StringTransformer::class)]
        public string|Optional $address,
        #[WithTransformer(StringTransformer::class)]
        public string|null|Optional $address_2,
        #[WithTransformer(StringTransformer::class)]
        public string|null|Optional $area,
        #[WithTransformer(StringTransformer::class)]
        public string|Optional $postal_code,
        public float|Optional $latitude,
        public float|Optional $longitude,
    ) {
    }

    public static function rules(): array
    {
        return [
            'name' => 'string',
            'company_number' => 'string',
            'email' => 'email',
            'phone' => 'string',
            'user_ids' => 'array|min:1',
            'user_ids.*' => 'numeric|exists:users,id',
            // address
            'city_id' => 'numeric|exists:cities,id',
            'address' => 'string',
            'address_2' => 'nullable|string',
            'area' => 'nullable|string',
            'postal_code' => 'string',
            'latitude' => 'numeric',
            'longitude' => 'numeric',
        ];
    }
}
