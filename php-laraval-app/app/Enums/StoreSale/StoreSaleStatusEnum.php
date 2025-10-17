<?php

namespace App\Enums\StoreSale;

use ArchTech\Enums\InvokableCases;
use ArchTech\Enums\Values;

enum StoreSaleStatusEnum: string
{
    use InvokableCases;
    use Values;

    case Pending = 'pending';
    case Paid = 'paid';
    case Cancelled = 'cancelled';
    case Closed = 'closed';
}
