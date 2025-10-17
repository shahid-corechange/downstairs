<?php

namespace App\DTOs\User;

use App\DTOs\BaseData;
use App\Rules\UserUniqueCellphone;
use App\Rules\SwedishSocialSecurityNumber;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\WithTransformer;
use Spatie\LaravelData\Mappers\CamelCaseMapper;
use Spatie\LaravelData\Optional;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapInputName(SnakeCaseMapper::class)]
class SignupCustomerRequestDTO extends BaseData
{
    public function __construct(
        public string $first_name,
        public string $last_name,
        public string $email,
        public string $cellphone,
        public string $identity_number,
    ) {
    }

    public static function rules(): array
    {
        return [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'cellphone' => ['required', 'string', 'max:16', new UserUniqueCellphone()],
            'identity_number' => ['required', 'string', new SwedishSocialSecurityNumber()],
        ];
    }
}