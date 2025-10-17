<?php

namespace App\Enums\Order;

use ArchTech\Enums\InvokableCases;
use ArchTech\Enums\Values;

/**
 * Enum: card, invoice, stripe, swish
 */
enum OrderPaidByEnum: string
{
    use InvokableCases;
    use Values;

    case Card = 'card';
    case Invoice = 'invoice';
    case Stripe = 'stripe';
    case Swish = 'swish';
}
