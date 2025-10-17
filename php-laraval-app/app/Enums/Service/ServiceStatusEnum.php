<?php

namespace App\Enums\Service;

use ArchTech\Enums\InvokableCases;
use ArchTech\Enums\Values;

/**
 * ENUM: available, unavailable, deleted
 */
enum ServiceStatusEnum: string
{
    use InvokableCases;
    use Values;

    case Available = 'available';
    case Unavailable = 'unavailable';
    case Deleted = 'deleted';
}
