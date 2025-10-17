<?php

namespace App\Enums\Subscription;

use ArchTech\Enums\From;
use ArchTech\Enums\InvokableCases;
use ArchTech\Enums\Names;
use ArchTech\Enums\Options;
use ArchTech\Enums\Values;

/**
 * ENUM: 52 (Weeks)
 */
enum SubscriptionRefillSequenceEnum: int
{
    use From;
    use InvokableCases;
    use Names;
    use Options;
    use Values;

    case OneYear = 52;
}
