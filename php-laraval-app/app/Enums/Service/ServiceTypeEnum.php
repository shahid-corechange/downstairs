<?php

namespace App\Enums\Service;

use ArchTech\Enums\InvokableCases;
use ArchTech\Enums\Values;

/**
 * ENUM: cleaning, laundry
 */
enum ServiceTypeEnum: string
{
    use InvokableCases;
    use Values;

    case Cleaning = 'cleaning';
    case Laundry = 'laundry';
}
