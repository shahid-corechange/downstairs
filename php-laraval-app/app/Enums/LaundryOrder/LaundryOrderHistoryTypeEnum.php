<?php

namespace App\Enums\LaundryOrder;

use ArchTech\Enums\InvokableCases;
use ArchTech\Enums\Values;

enum LaundryOrderHistoryTypeEnum: string
{
    use InvokableCases;
    use Values;

    case Order = 'order';
    case Product = 'product';
    case Notification = 'notification';
}
