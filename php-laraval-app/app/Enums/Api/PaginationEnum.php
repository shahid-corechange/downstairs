<?php

namespace App\Enums\Api;

use ArchTech\Enums\InvokableCases;
use ArchTech\Enums\Values;

enum PaginationEnum: string
{
    use InvokableCases;
    use Values;

    case Cursor = 'cursor';
    case Page = 'page';
}
