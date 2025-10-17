<?php

namespace App\Enums\Azure\NotificationHub;

use ArchTech\Enums\InvokableCases;
use ArchTech\Enums\Values;

enum NotificationHubEnum: string
{
    use InvokableCases;
    use Values;

    case Customer = 'hub-customer-downstairs';
    case Employee = 'hub-employee-downstairs';
}
