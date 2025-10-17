<?php

namespace App\Enums\ScheduleCleaning;

use ArchTech\Enums\InvokableCases;
use ArchTech\Enums\Values;

/**
 * ENUM: credit, invoice
 */
enum CleaningProductPaymentMethodEnum: string
{
    use InvokableCases;
    use Values;

    case Credit = 'credit';
    case Invoice = 'invoice';
}
