<?php

namespace App\Enums\Invoice;

use ArchTech\Enums\InvokableCases;
use ArchTech\Enums\Values;

/**
 * Enum: open, cancel, sent, paid
 */
enum InvoiceStatusEnum: string
{
    use InvokableCases;
    use Values;

    case Open = 'open';
    case Created = 'created';
    case Cancel = 'cancel';
    case Sent = 'sent';
    case Paid = 'paid';
}
