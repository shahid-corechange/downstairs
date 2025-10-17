<?php

namespace App\Enums\FixedPrice;

use ArchTech\Enums\InvokableCases;
use ArchTech\Enums\Values;

enum FixedPriceRowTypeEnum: string
{
    use InvokableCases;
    use Values;

    case Service = 'service';
    case Material = 'material';
    case DriveFee = 'drive fee';
    case Laundry = 'laundry';
}
