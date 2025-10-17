<?php

namespace App\Enums\Product;

use ArchTech\Enums\InvokableCases;
use ArchTech\Enums\Options;
use ArchTech\Enums\Values;

enum ProductUnitEnum: string
{
    use InvokableCases;
    use Options;
    use Values;

    case Packaging = 'förp';
    case Hours = 'h';
    case Kilometers = 'km';
    case Piece = 'st';
    case Outlay = 'utl';
}
