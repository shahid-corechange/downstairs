<?php

namespace App\Enums\Fortnox;

/**
 * Enum: "PRINT" "EMAIL" "PRINTSERVICE"
 */
enum FortnoxInvoiceTypeEnum: string
{
    case Print = 'PRINT';
    case Email = 'EMAIL';
    case Printservice = 'PRINTSERVICE';
}
