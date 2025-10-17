<?php

namespace App\DTOs\Auth;

use App\DTOs\BaseData;
use Illuminate\Validation\Rules;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Mappers\CamelCaseMapper;

#[MapInputName(CamelCaseMapper::class)]
class NewPasswordRequestDTO extends BaseData
{
    public function __construct(
        public ?string $payload,
        public string $hash,
        public string $email,
        public string $password,
        public string $password_confirmation
    ) {
    }

    public static function rules(): array
    {
        return [
            'payload' => 'nullable|string',
            'hash' => 'required',
            'email' => 'required|email',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'password_confirmation' => 'required',
        ];
    }
}
