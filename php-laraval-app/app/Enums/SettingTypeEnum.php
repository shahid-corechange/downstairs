<?php

namespace App\Enums;

use ArchTech\Enums\InvokableCases;
use ArchTech\Enums\Values;

/**
 * ENUM: string, integer, boolean, float
 */
enum SettingTypeEnum: string
{
    use InvokableCases;
    use Values;

    case String = 'string';
    case Integer = 'integer';
    case Boolean = 'boolean';
    case Float = 'float';
}
