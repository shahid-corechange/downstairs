<?php

namespace App\Enums\Subscription;

use ArchTech\Enums\InvokableCases;
use ArchTech\Enums\Values;

/**
 * ENUM: active, inactive, expired, canceled, deleted
 */
enum SubscriptionStatusEnum: string
{
    use InvokableCases;
    use Values;

    case Active = 'active';
    case Inactive = 'inactive';
    case Expired = 'expired';
    case Canceled = 'canceled';
    case Deleted = 'deleted';
}
