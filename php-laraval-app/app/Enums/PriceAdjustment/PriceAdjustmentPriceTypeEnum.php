<?php

namespace App\Enums\PriceAdjustment;

use ArchTech\Enums\InvokableCases;
use ArchTech\Enums\Values;

/**
 * ENUM: fixed_price_with_vat, dynamic_percentage, dynamic_fixed_with_vat
 */
enum PriceAdjustmentPriceTypeEnum: string
{
    use InvokableCases;
    use Values;

    case FixedPriceWithVat = 'fixed_price_with_vat';
    case DynamicPercentage = 'dynamic_percentage';
    case DynamicFixedithVat = 'dynamic_fixed_with_vat';
}
