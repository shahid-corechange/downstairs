<?php

namespace App\DTOs\Store;

use App\DTOs\BaseData;
use App\Transformers\StringTransformer;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\WithTransformer;
use Spatie\LaravelData\Mappers\CamelCaseMapper;
use Spatie\LaravelData\Optional;

#[MapInputName(CamelCaseMapper::class)]
class CreateStoreRequestDTO extends BaseData
{
    public function __construct(
        #[WithTransformer(StringTransformer::class)]
        public string $name,
        #[WithTransformer(StringTransformer::class)]
        public string $company_number,
        #[WithTransformer(StringTransformer::class)]
        public string $email,
        #[WithTransformer(StringTransformer::class)]
        public string $phone,
        public array $user_ids,
        // address
        public int $city_id,
        #[WithTransformer(StringTransformer::class)]
        public string $address,
        #[WithTransformer(StringTransformer::class)]
        public string|null|Optional $address_2,
        #[WithTransformer(StringTransformer::class)]
        public string|null|Optional $area,
        #[WithTransformer(StringTransformer::class)]
        public string $postal_code,
        public float $latitude,
        public float $longitude,
    ) {
    }

    public static function rules(): array
    {
        return [
            'name' => 'required|string',
            'company_number' => 'required|string',
            'email' => 'required|email',
            'phone' => 'required|string',
            'user_ids' => 'required|array|min:1',
            'user_ids.*' => 'required|numeric|exists:users,id',
            // address
            'city_id' => 'required|numeric|exists:cities,id',
            'address' => 'required|string',
            'address_2' => 'nullable|string',
            'area' => 'nullable|string',
            'postal_code' => 'required|string',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ];
    }
}
