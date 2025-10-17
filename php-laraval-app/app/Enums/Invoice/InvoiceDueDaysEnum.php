<?php

namespace App\Enums\Invoice;

use ArchTech\Enums\InvokableCases;
use ArchTech\Enums\Values;

enum InvoiceDueDaysEnum: int
{
    use InvokableCases;
    use Values;

    case Zero = 0;
    case Ten = 10;
    case Fifteen = 15;
    case Twenty = 20;
    case Thirty = 30;
}
