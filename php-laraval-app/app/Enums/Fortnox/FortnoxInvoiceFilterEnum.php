<?php

namespace App\Enums\Fortnox;

use ArchTech\Enums\InvokableCases;
use ArchTech\Enums\Values;

/**
 * Enum: "cancelled" "fullypaid" "unpaid" "unpaidoverdue" "unbooked"
 */
enum FortnoxInvoiceFilterEnum: string
{
    use InvokableCases;
    use Values;

    case Cancelled = 'cancelled';
    case FullyPaid = 'fullypaid';
    case Unpaid = 'unpaid';
    case UnpaidOverdue = 'unpaidoverdue';
    case Unbooked = 'unbooked';
}
