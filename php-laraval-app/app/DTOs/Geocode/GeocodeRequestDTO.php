<?php

namespace App\DTOs\Geocode;

use App\DTOs\BaseData;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Mappers\CamelCaseMapper;

#[MapInputName(CamelCaseMapper::class)]
class GeocodeRequestDTO extends BaseData
{
    public function __construct(
        public string $address,
    ) {
    }

    public static function rules(): array
    {
        return [
            'address' => 'required|string',
        ];
    }
}
