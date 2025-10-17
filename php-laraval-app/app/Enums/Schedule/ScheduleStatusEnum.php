<?php

namespace App\Enums\Schedule;

use ArchTech\Enums\InvokableCases;
use ArchTech\Enums\Values;

/**
 * ENUM: booked, cancel, progress, done
 */
enum ScheduleStatusEnum: string
{
    use InvokableCases;
    use Values;

    /**
     * When first time the schedule created.
     */
    case Booked = 'booked';

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
