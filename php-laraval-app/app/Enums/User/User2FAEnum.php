<?php

namespace App\Enums\User;

use ArchTech\Enums\InvokableCases;
use ArchTech\Enums\Values;

enum User2FAEnum: string
{
    use InvokableCases;
    use Values;

    // The user account has two-factor authentication enabled via Email.
    case Email = 'email';

    // The user account has two-factor authentication enabled via SMS.
    case SMS = 'sms';

    // The user account does not have two-factor authentication enabled.
    case Disabled = 'disabled';
}
