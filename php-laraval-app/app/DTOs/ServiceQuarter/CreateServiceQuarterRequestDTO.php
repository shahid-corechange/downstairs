<?php

namespace App\DTOs\ServiceQuarter;

use App\DTOs\BaseData;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Mappers\CamelCaseMapper;

#[MapInputName(CamelCaseMapper::class)]
class CreateServiceQuarterRequestDTO extends BaseData
{
    public function __construct(
        public int $service_id,
        public int $min_square_meters,
        public int $max_square_meters,
        public int $quarters,
    ) {
    }

    public static function rules(): array
    {
        return [
            'service_id' => 'required|numeric|exists:services,id',
            'min_square_meters' => 'required|numeric:min:0',
            'max_square_meters' => 'required|numeric|min:0|gt:min_square_meters',
            'quarters' => 'required|numeric|min:1',
        ];
    }
}
