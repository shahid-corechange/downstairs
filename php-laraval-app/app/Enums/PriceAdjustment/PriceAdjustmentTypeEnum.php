<?php

namespace App\Enums\PriceAdjustment;

use ArchTech\Enums\InvokableCases;
use ArchTech\Enums\Values;

/**
 * ENUM: service, product, fixed_price
 */
enum PriceAdjustmentTypeEnum: string
{
    use InvokableCases;
    use Values;

    case Service = 'service';
    case Addon = 'addon';
    case Product = 'product';
    case FixedPrice = 'fixed_price';
}
