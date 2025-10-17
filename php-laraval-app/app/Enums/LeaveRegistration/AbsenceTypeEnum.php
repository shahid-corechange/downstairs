<?php

namespace App\Enums\LeaveRegistration;

use ArchTech\Enums\InvokableCases;
use ArchTech\Enums\Values;

enum AbsenceTypeEnum: string
{
    use InvokableCases;
    use Values;

    case SickLeave = 'SJK';
    case SickChildLeave = 'VAB';
    case Vacation = 'SEM';
    case UnpaidVacation = 'TJL';
}
