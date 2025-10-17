<?php

namespace App\Enums\Fortnox;

use ArchTech\Enums\InvokableCases;
use ArchTech\Enums\Values;

/**
 * Enum: customernumber, name
 */
enum FortnoxCustomerSortEnum: string
{
    use InvokableCases;
    use Values;

    case CustomerNumber = 'customernumber';
    case Name = 'name';
}
