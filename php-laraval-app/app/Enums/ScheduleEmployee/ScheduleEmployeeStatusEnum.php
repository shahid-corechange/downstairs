<?php

namespace App\Enums\ScheduleEmployee;

use ArchTech\Enums\InvokableCases;
use ArchTech\Enums\Values;

/**
 * ENUM: pending, cancel, progress, done
 */
enum ScheduleEmployeeStatusEnum: string
{
    use InvokableCases;
    use Values;

    /**
     * When the schedule is not started yet.
     */
    case Pending = 'pending';

    /**
     * When the schedule is canceled.
     */
    case Cancel = 'cancel';

    /**
     * When the worker already clock-in.
     */
    case Progress = 'progress';

    /**
     * When the schedule is done.
     */
    case Done = 'done';
}
