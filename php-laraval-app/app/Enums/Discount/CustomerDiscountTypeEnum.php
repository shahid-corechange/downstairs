<?php

namespace App\Enums\Discount;

use ArchTech\Enums\InvokableCases;
use ArchTech\Enums\Values;

enum CustomerDiscountTypeEnum: string
{
    use InvokableCases;
    use Values;

    case Laundry = 'laundry';
    case Cleaning = 'cleaning';
}
