<?php

namespace App\Enums\Credit;

use ArchTech\Enums\InvokableCases;
use ArchTech\Enums\Values;

enum CreditTypeEnum: string
{
    use InvokableCases;
    use Values;

    case Refund = 'refund';
    case Granted = 'granted';
}
