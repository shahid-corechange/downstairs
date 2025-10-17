<?php

namespace App\Enums\Contact;

use ArchTech\Enums\InvokableCases;
use ArchTech\Enums\Values;

/**
 * Enum: Primary, Invoice, Rut
 */
enum ContactTypeEnum: string
{
    use InvokableCases;
    use Values;

    case Primary = 'primary';
    case Invoice = 'invoice';
    case Rut = 'rut';
}
