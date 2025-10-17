<?php

namespace App\DTOs\Auth;

use App\DTOs\BaseData;
use App\Rules\EmailOrPhoneNumber;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Mappers\CamelCaseMapper;

#[MapInputName(CamelCaseMapper::class)]
class LoginPortalRequestDTO extends BaseData
{
    public function __construct(
        public string $user,
        public string $password,
        public bool $remember = false,
    ) {
    }

    public static function rules(): array
    {
        return [
            'user' => ['required', 'string', new EmailOrPhoneNumber()],
            'password' => 'required|string',
            'remember' => 'boolean',
        ];
    }
}
