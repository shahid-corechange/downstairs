<?php

namespace App\DTOs\Auth;

use App\DTOs\BaseData;

class ForgetPasswordRequestDTO extends BaseData
{
    public function __construct(
        public string $email
    ) {
    }

    public static function rules(): array
    {
        return [
            'email' => 'required|email',
        ];
    }
}
