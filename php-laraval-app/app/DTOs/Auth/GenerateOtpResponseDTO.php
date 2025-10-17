<?php

namespace App\DTOs\Auth;

use App\DTOs\BaseData;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapInputName(SnakeCaseMapper::class)]
class GenerateOtpResponseDTO extends BaseData
{
    public function __construct(
        public string $cellphone,
        public ?string $otp,
        public string $expireAt,
    ) {
    }
}
