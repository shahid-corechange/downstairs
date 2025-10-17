<?php

namespace App\Enums\Credit;

use ArchTech\Enums\InvokableCases;
use ArchTech\Enums\Values;

enum CreditTransactionTypeEnum: string
{
    use InvokableCases;
    use Values;

    case Payment = 'payment';
    case Refund = 'refund';
    case Granted = 'granted';
    case Updated = 'updated';
    case Removed = 'removed';
}
