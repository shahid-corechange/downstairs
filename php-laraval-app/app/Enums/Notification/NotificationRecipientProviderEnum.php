<?php

namespace App\Enums\Notification;

use ArchTech\Enums\InvokableCases;
use ArchTech\Enums\Values;

/**
 * Enum: APNS, Expo, FCM
 */
enum NotificationRecipientProviderEnum: string
{
    use InvokableCases;
    use Values;

    case APNS = 'apns';
    case Expo = 'expo';
    case FCM = 'fcm';
}
