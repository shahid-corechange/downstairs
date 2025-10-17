<?php

namespace App\Enums\Api;

use ArchTech\Enums\InvokableCases;
use ArchTech\Enums\Values;

/**
 * ENUM: asc, desc,
 */
enum SortEnum: string
{
    use InvokableCases;
    use Values;

    case Ascending = 'asc';
    case Descending = 'desc';
}
