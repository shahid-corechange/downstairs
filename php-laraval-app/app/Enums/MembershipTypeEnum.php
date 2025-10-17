<?php

namespace App\Enums;

use ArchTech\Enums\InvokableCases;
use ArchTech\Enums\Values;

/**
 * Enum: private, company
 */
enum MembershipTypeEnum: string
{
    use InvokableCases;
    use Values;

    case Private = 'private';
    case Company = 'company';
}
