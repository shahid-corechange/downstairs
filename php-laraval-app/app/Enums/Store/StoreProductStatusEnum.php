<?php

namespace App\Enums\Store;

use ArchTech\Enums\InvokableCases;
use ArchTech\Enums\Values;

/**
 * ENUM: active, inactive
 */
enum StoreProductStatusEnum: string
{
    use InvokableCases;
    use Values;

    case Active = 'active';
    case Inactive = 'inactive';
}
