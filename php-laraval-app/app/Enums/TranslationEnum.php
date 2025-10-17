<?php

namespace App\Enums;

use ArchTech\Enums\InvokableCases;
use ArchTech\Enums\Values;

enum TranslationEnum: string
{
    use InvokableCases;
    use Values;

    case English = 'en_US';
    case Norwegian = 'nn_NO';
    case Swedish = 'sv_SE';
}
