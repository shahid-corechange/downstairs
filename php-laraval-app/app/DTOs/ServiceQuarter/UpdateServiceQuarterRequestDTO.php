<?php

namespace App\DTOs\ServiceQuarter;

use App\DTOs\BaseData;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Mappers\CamelCaseMapper;
use Spatie\LaravelData\Optional;

#[MapInputName(CamelCaseMapper::class)]
class UpdateServiceQuarterRequestDTO extends BaseData
{
    public function __construct(
        public null|Optional|int $min_square_meters,
        public null|Optional|int $max_square_meters,
        public null|Optional|int $quarters,
    ) {
    }

    public static function rules(): array
    {
        return [
            'min_square_meters' => 'nullable|numeric:min:0',
            'max_square_meters' => 'nullable|numeric|min:0|gt:min_square_meters',
            'quarters' => 'nullable|numeric|min:1',
        ];
    }
}
