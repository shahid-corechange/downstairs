<?php

namespace App\Enums\FixedPrice;

use ArchTech\Enums\InvokableCases;
use ArchTech\Enums\Values;

enum FixedPriceTypeEnum: string
{
    use InvokableCases;
    use Values;

    case Cleaning = 'cleaning';
    case Laundry = 'laundry';
    case CleaningAndLaundry = 'cleaning_and_laundry';
}
