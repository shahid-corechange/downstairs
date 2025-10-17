<?php

namespace App\Enums\Fortnox;

use ArchTech\Enums\InvokableCases;
use ArchTech\Enums\Values;

/**
 * Enum: active, inactive
 */
enum FortnoxFilterTypeEnum: string
{
    use InvokableCases;
    use Values;

    case Active = 'active';
    case Inactive = 'inactive';
}
