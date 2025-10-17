<?php

namespace App\Enums\Subscription;

use ArchTech\Enums\InvokableCases;
use ArchTech\Enums\Values;

/**
 * ENUM: home, office, restaurant, industry
 */
enum SubscriptionTypeEnum: string
{
    use InvokableCases;
    use Values;

    case Home = 'home';
    case Office = 'office';
    case Restaurant = 'restaurant';
    case Industry = 'industry';
}
