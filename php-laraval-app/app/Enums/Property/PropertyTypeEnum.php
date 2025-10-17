<?php

namespace App\Enums\Property;

use ArchTech\Enums\InvokableCases;
use ArchTech\Enums\Values;

/**
 * Enum: house, apartment, office, warehouse, restaurant
 */
enum PropertyTypeEnum: string
{
    use InvokableCases;
    use Values;

    case House = 'house';
    case Apartment = 'apartment';
    case Office = 'office';
    case Warehouse = 'warehouse';
    case Restaurant = 'restaurant';
}
