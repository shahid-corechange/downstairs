<?php

namespace App\Enums;

use ArchTech\Enums\InvokableCases;
use ArchTech\Enums\Values;

/**
 * ENUM: 0, 6, 12, 25
 */
enum VatNumbersEnum: int
{
    use InvokableCases;
    use Values;

    case Zero = 0;
    case Six = 6;
    case Twelve = 12;
    case TwentyFive = 25;
}
