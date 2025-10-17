<?php

namespace App\DTOs\Auth;

use App\DTOs\BaseData;

class GenerateOtpRequestDTO extends BaseData
{
    public function __construct(
        public string $cellphone
    ) {
    }

    public static function rules(): array
    {
        return [
            'cellphone' => 'required|exists:users,cellphone',
        ];
    }
}
