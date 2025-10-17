<?php

namespace App\Enums\ScheduleLaundry;

use ArchTech\Enums\InvokableCases;
use ArchTech\Enums\Values;

/**
 * ENUM: pickup, delivery
 */
enum ScheduleLaundryTypeEnum: string
{
    use InvokableCases;
    use Values;

    case Pickup = 'pickup';
    case Delivery = 'delivery';
}
