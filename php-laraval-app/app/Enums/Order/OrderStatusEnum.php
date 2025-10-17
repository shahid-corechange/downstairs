<?php

namespace App\Enums\Order;

use ArchTech\Enums\InvokableCases;
use ArchTech\Enums\Values;

/**
 * Enum: cancel, progress, done
 */
enum OrderStatusEnum: string
{
    use InvokableCases;
    use Values;

    case Draft = 'draft';
    case Cancel = 'cancel';
    case Progress = 'progress';
    case Done = 'done';
}
