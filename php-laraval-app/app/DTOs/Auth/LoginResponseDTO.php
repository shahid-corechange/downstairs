<?php

namespace App\DTOs\Auth;

use App\DTOs\BaseData;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapInputName(SnakeCaseMapper::class)]
class LoginResponseDTO extends BaseData
{
    public function __construct(
        public ?string $accessToken,
        public ?string $refreshToken,
    ) {
    }
}
