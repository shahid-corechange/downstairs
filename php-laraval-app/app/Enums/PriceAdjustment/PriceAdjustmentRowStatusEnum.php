<?php

namespace App\Enums\PriceAdjustment;

use ArchTech\Enums\InvokableCases;
use ArchTech\Enums\Values;

/**
 * ENUM: pending, done
 */
enum PriceAdjustmentRowStatusEnum: string
{
    use InvokableCases;
    use Values;

    case Pending = 'pending';
    case Done = 'done';
}
