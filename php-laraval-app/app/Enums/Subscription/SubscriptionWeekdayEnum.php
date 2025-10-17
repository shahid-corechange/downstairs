<?php

namespace App\Enums\Subscription;

use ArchTech\Enums\InvokableCases;
use ArchTech\Enums\Options;
use ArchTech\Enums\Values;

/**
 * ENUM: 1, 2, 3, 4, 5, 6, 7
 */
enum SubscriptionWeekdayEnum: int
{
    use InvokableCases;
    use Options;
    use Values;

    case Monday = 1;
    case Tuesday = 2;
    case Wednesday = 3;
    case Thursday = 4;
    case Friday = 5;
    case Saturday = 6;
    case Sunday = 7;
}
