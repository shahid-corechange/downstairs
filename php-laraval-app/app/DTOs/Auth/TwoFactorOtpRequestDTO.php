<?php

namespace App\DTOs\Auth;

use App\DTOs\BaseData;
use App\Rules\EmailOrPhoneNumber;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Mappers\CamelCaseMapper;

#[MapInputName(CamelCaseMapper::class)]
class TwoFactorOtpRequestDTO extends BaseData
{
    public function __construct(
        public string $user,
        public string $password
    ) {
    }

    public static function rules(): array
    {
        return [
            'user' => ['required', 'string', new EmailOrPhoneNumber()],
            'password' => 'required|string',
        ];
    }
}
