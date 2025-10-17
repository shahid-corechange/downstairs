<?php

namespace App\Enums\ScheduleCleaning;

use ArchTech\Enums\InvokableCases;
use ArchTech\Enums\Values;

/**
 * ENUM: draft, pending, cancel, progress, booked, change, done, invoiced
 */
enum ScheduleCleaningStatusEnum: string
{
    use InvokableCases;
    use Values;

    /**
     * When admin not publish the schedule yet.
     */
    case Draft = 'draft';

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
     * When first time the schedule created.
     */
    case Booked = 'booked';

    /**
     * When the worker clock-in/out different
     * with the schedule start/end at.
     */
    case Change = 'change';

    /**
     * When the schedule is done.
     */
    case Done = 'done';

    /**
     * When the schedule is invoiced.
     */
    case Invoiced = 'invoiced';
}
