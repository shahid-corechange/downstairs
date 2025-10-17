<?php

namespace App\Enums\Azure\NotificationHub;

use ArchTech\Enums\InvokableCases;
use ArchTech\Enums\Values;

enum PlatformTypeEnum: string
{
    use InvokableCases;
    use Values;

    case Android = 'android';
    case IOS = 'ios';
}
