<?php

namespace App\DTOs\Credit;

use App\DTOs\BaseData;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Mappers\CamelCaseMapper;

#[MapInputName(CamelCaseMapper::class)]
class UpdateCreditRequestDTO extends BaseData
{
    public function __construct(
        public int $amount,
        public string $description,
        public string $valid_until,
    ) {
    }

    public static function rules(): array
    {
        return [
            'amount' => 'required|numeric|gte:0',
            'description' => 'required|string',
            'valid_until' => 'date|after:today',
        ];
    }
}
