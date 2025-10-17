<?php

namespace App\Enums\Fortnox;

/**
 * Enum: "SEVAT" "SEREVERSEDVAT" "EUREVERSEDVAT" "EUVAT" "EXPORT"
 */
enum FortnoxVatEnum: string
{
    case Sevat = 'SEVAT';
    case Sereversedvat = 'SEREVERSEDVAT';
    case Eureversedvat = 'EUREVERSEDVAT';
    case Euvat = 'EUVAT';
    case Export = 'EXPORT';
}
