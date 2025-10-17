<?php

namespace App\Enums\Property;

use ArchTech\Enums\InvokableCases;
use ArchTech\Enums\Values;

/**
 * ENUM: active, inactive
 */
enum PropertyStatusEnum: string
{
    use InvokableCases;
    use Values;

    case Active = 'active';
    case Inactive = 'inactive';
}
