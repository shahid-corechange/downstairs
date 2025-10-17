<?php

namespace App\Enums\Product;

use ArchTech\Enums\InvokableCases;
use ArchTech\Enums\Values;

/**
 * ENUM: available, unavailable, deleted
 */
enum ProductStatusEnum: string
{
    use InvokableCases;
    use Values;

    case Available = 'available';
    case Unavailable = 'unavailable';
    case Deleted = 'deleted';
}
