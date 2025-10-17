<?php

namespace App\Enums\LaundryOrder;

use ArchTech\Enums\InvokableCases;
use ArchTech\Enums\Values;

/**
 * ENUM: pickup, delivery
 */
enum LaundryOrderScheduleTypeEnum: string
{
    use InvokableCases;
    use Values;

    case Pickup = 'pickup';
    case Delivery = 'delivery';
}
