<?php

namespace App\Enums\Subscription;

use ArchTech\Enums\InvokableCases;
use ArchTech\Enums\Values;

/**
 * ENUM: cleaning, major, laundry, windows
 */
enum SubscriptionPlanEnum: string
{
    use InvokableCases;
    use Values;

    case Cleaning = 'cleaning';
    case Major = 'major';
    case Laundry = 'laundry';
    case Windows = 'windows';
}
