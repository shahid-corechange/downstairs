<?php

namespace App\Enums;

use ArchTech\Enums\InvokableCases;
use ArchTech\Enums\Values;

enum PaymentMethodEnum: string
{
    use InvokableCases;
    use Values;

    case CreditCard = 'credit_card';
    case Cash = 'cash';
    case Invoice = 'invoice';
}
