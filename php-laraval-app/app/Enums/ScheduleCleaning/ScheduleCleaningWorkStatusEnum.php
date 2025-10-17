<?php

namespace App\Enums\ScheduleCleaning;

use ArchTech\Enums\InvokableCases;
use ArchTech\Enums\Values;

/**
 * ENUM: ok, started late, ended late, not started
 */
enum ScheduleCleaningWorkStatusEnum: string
{
    use InvokableCases;
    use Values;

    /**
     * When schedule started or ended as planned.
     */
    case OK = 'ok';

    /**
     * When the schedule is started late.
     */
    case StartedLate = 'started late';

    /**
     * When the schedule is ended late.
     */
    case EndedLate = 'ended late';

    /**
     * When the schedule is not started yet.
     */
    case NotStarted = 'not started';
}
