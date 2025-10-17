<?php

namespace App\Enums\Contact;

use ArchTech\Enums\InvokableCases;
use ArchTech\Enums\Values;

/**
 * Enum: Private, Business
 */
enum ContactUserTypeEnum: string
{
    use InvokableCases;
    use Values;

    case Private = 'Private';
    case Business = 'Business';
}
