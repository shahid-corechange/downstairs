<?php

namespace App\Enums\Subscription;

use ArchTech\Enums\From;
use ArchTech\Enums\InvokableCases;
use ArchTech\Enums\Names;
use ArchTech\Enums\Options;
use ArchTech\Enums\Values;

/**
 * ENUM: 0, 1, 2, 3, 4, 8, 13, 26, 52 (Weeks)
 */
enum SubscriptionFrequencyEnum: int
{
    use From;
    use InvokableCases;
    use Names;
    use Options;
    use Values;

    case Once = 0;
    case EveryWeek = 1;
    case EveryTwoWeeks = 2;
    case EveryThreeWeeks = 3;
    case EveryFourWeeks = 4;
    case EveryEightWeeks = 8;
    case EveryThirteenWeeks = 13;
    case Semiannual = 26;
    case Annually = 52;
}
