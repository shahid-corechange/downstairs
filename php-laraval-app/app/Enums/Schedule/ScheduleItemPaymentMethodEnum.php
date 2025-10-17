<?php

namespace App\Enums\Schedule;

use ArchTech\Enums\InvokableCases;
use ArchTech\Enums\Values;

enum ScheduleItemPaymentMethodEnum: string
{
    use InvokableCases;
    use Values;

    case Credit = 'credit';
    case Invoice = 'invoice';
}
