<?php

namespace App\Enums\Deviation;

use ArchTech\Enums\InvokableCases;
use ArchTech\Enums\Values;

/**
 * Enum: start wrong position, stop wrong position, start wrong time, stop wrong time,
 *  cannot start, incomplete task, not started, finished early, canceled, partly canceled
 */
enum DeviationTypeEnum: string
{
    use InvokableCases;
    use Values;

    case StartWrongPosition = 'start wrong position';
    case StopWrongPosition = 'stop wrong position';
    case StartWrongTime = 'start wrong time';
    case StopWrongTime = 'stop wrong time';
    case CannotStart = 'cannot start';
    case IncompleteTask = 'incomplete task';
    case NotStarted = 'not started';
    case FinishedEarly = 'finished early';
    case Canceled = 'canceled';
    case PartlyCanceled = 'partly canceled';
}
