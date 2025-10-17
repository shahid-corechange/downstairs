<?php

namespace App\DTOs\Credit;

use App\DTOs\BaseData;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Mappers\CamelCaseMapper;
use Spatie\LaravelData\Optional;

#[MapInputName(CamelCaseMapper::class)]
class CreateCreditRequestDTO extends BaseData
{
    public function __construct(
        public int $user_id,
        public int $amount,
        public string $description,
        public string|Optional $valid_until,
    ) {
    }

    public static function rules(): array
    {
        return [
            'user_id' => 'required|numeric|exists:users,id',
            'amount' => 'required|numeric|not_in:0',
            'description' => 'required|string',
            'valid_until' => 'date|after:today',
        ];
    }
}
