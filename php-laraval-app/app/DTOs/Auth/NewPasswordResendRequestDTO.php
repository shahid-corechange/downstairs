<?php

namespace App\DTOs\Auth;

use App\DTOs\BaseData;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Mappers\CamelCaseMapper;

#[MapInputName(CamelCaseMapper::class)]
class NewPasswordResendRequestDTO extends BaseData
{
    public function __construct(
        public ?string $payload,
    ) {
    }

    public static function rules(): array
    {
        return [
            'payload' => 'nullable|string',
        ];
    }
}
