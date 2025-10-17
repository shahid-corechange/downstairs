<?php

namespace App\Enums\Product;

use ArchTech\Enums\InvokableCases;
use ArchTech\Enums\Options;
use ArchTech\Enums\Values;

/**
 * ENUM: 0, 1, 2, 3
 */
enum ProductTypeEnum: int
{
    use InvokableCases;
    use Options;
    use Values;

    case Item = 0; // itm
    case SquareMeter = 1; // M2
    case Centimeter = 2; // Cm
    case Meter = 3; // M
}
