<?php

namespace App\Enums\Invoice;

use ArchTech\Enums\InvokableCases;
use ArchTech\Enums\Values;

enum InvoiceCategoryEnum: string
{
    use InvokableCases;
    use Values;

    case Invoice = 'invoice';
    case CashInvoice = 'cashinvoice';
}
