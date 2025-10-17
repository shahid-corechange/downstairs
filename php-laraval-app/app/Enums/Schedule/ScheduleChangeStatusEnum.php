<?php

namespace App\Enums\Schedule;

use ArchTech\Enums\InvokableCases;
use ArchTech\Enums\Values;

/**
 * ENUM: pending, rejected, approved, canceled, handled
 */
enum ScheduleChangeStatusEnum: string
{
    use InvokableCases;
    use Values;

    case Pending = 'pending';
    case Rejected = 'rejected';
    case Approved = 'approved';
    case Canceled = 'canceled';
    case Handled = 'handled';
}
