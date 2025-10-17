<?php

namespace App\DTOs\Auth;

use App\DTOs\BaseData;
use App\Enums\Azure\NotificationHub\PlatformTypeEnum;
use Illuminate\Validation\Rule;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Mappers\CamelCaseMapper;
use Spatie\LaravelData\Optional;

#[MapInputName(CamelCaseMapper::class)]
class LoginByEmailRequestDTO extends BaseData
{
    public function __construct(
        public string $email,
        public string $password,
        public string|Optional $device_token,
        public string $device_platform
    ) {
    }

    public static function rules(): array
    {
        return [
            'email' => 'required|string|email',
            'password' => 'required|string',
            'device_token' => 'string',
            'device_platform' => ['required', Rule::in(PlatformTypeEnum::values())],
        ];
    }
}
