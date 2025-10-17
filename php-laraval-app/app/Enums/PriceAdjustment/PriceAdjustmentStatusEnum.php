<?php

namespace App\Enums\PriceAdjustment;

use ArchTech\Enums\InvokableCases;
use ArchTech\Enums\Values;

/**
 * ENUM: pending, partial, done
 */
enum PriceAdjustmentStatusEnum: string
{
    use InvokableCases;
    use Values;

    case Pending = 'pending';
    case Partial = 'partial';
    case Done = 'done';
}
