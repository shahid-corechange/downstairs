<?php

namespace App\Enums\ScheduleCleaning;

use ArchTech\Enums\InvokableCases;
use ArchTech\Enums\Values;

/**
 * ENUM: credit, invoice
 */
enum CleaningItemPaymentMethodEnum: string
{
    use InvokableCases;
    use Values;

    case Credit = 'credit';
    case Invoice = 'invoice';
}
