<?php

namespace App\Enums\Notification;

use ArchTech\Enums\InvokableCases;
use ArchTech\Enums\Values;

/**
 * ENUM: register, unregister
 */
enum NotificationRegistrationActionEnum: string
{
    use InvokableCases;
    use Values;

    case Register = 'register';
    case Unregister = 'unregister';
}
