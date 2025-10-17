<?php

namespace App\Enums\WorkHour;

use ArchTech\Enums\InvokableCases;
use ArchTech\Enums\Values;

enum WorkHourTypeEnum: string
{
    use InvokableCases;
    use Values;

    // Calculate total time based on clock in and clock out.
    case Store = 'store';

    // Calculate total time based on first start time and last end time.
    case Schedule = 'schedule';
}
