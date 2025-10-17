<?php

namespace App\Enums\User;

use ArchTech\Enums\InvokableCases;
use ArchTech\Enums\Values;

enum UserNotificationMethodEnum: string
{
    use InvokableCases;
    use Values;

    // The user account has notification enabled via Email.
    case Email = 'email';

    // The user account has notification enabled via SMS.
    case SMS = 'sms';

    // The user account has notification enabled via the mobile app.
    case App = 'app';
}
