<?php

namespace App\DTOs\Subscription;

use App\DTOs\BaseData;
use App\Models\Subscription;

class SubscriptionLaundryOrderDTO extends BaseData
{
    public function __construct(
        public Subscription $subscription,
        public SubscriptionScheduleDTO $pickup_schedule,
        public ?SubscriptionScheduleDTO $delivery_schedule,
    ) {
    }
}
