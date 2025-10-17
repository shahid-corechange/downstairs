<?php

namespace App\Enums\Invoice;

use ArchTech\Enums\InvokableCases;
use ArchTech\Enums\Values;

enum InvoiceTypeEnum: string
{
    use InvokableCases;
    use Values;

    case Cleaning = 'cleaning';
    case Laundry = 'laundry';
    case CleaningAndLaundry = 'cleaning and laundry';
}
