<?php

namespace App\Enums\ScheduleCleaning;

use ArchTech\Enums\InvokableCases;
use ArchTech\Enums\Values;

/**
 * ENUM: pending, rejected, approved
 */
enum ScheduleCleaningChangeStatusEnum: string
{
    use InvokableCases;
    use Values;

    case Pending = 'pending';
    case Rejected = 'rejected';
    case Approved = 'approved';
    case Canceled = 'canceled';
    case Handled = 'handled';
}
