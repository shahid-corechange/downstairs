<?php

namespace App\Enums;

use ArchTech\Enums\InvokableCases;
use ArchTech\Enums\Values;

/**
 * ENUM: 0, 6, 12, 25
 */
enum RutNumbersEnum: int
{
    use InvokableCases;
    use Values;

    case Zero = 0;
    case TwentyFive = 25;
    case Fifty = 50;
}
