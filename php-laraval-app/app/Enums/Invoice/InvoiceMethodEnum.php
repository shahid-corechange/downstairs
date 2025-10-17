<?php

namespace App\Enums\Invoice;

use ArchTech\Enums\InvokableCases;
use ArchTech\Enums\Values;

/**
 * Enum: email, print
 */
enum InvoiceMethodEnum: string
{
    use InvokableCases;
    use Values;

    case Email = 'email';
    case Print = 'print';
}
