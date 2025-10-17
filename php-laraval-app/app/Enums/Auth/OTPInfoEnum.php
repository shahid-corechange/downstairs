<?php

namespace App\Enums\Auth;

use ArchTech\Enums\InvokableCases;
use ArchTech\Enums\Values;

enum OTPInfoEnum: string
{
    use InvokableCases;
    use Values;

    case Login = 'login';
    case UpdateCellphone = '%s_update_cellphone';
    case TwoFactor = '2fa';
}
