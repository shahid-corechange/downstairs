<?php

namespace App\DTOs\Subscription;

use App\DTOs\BaseData;

class SubscriptionScheduleDTO extends BaseData
{
    public function __construct(
        public ?int $subscription_id,
        public ?int $user_id,
        public ?int $service_id,
        public int $team_id,
        public ?int $customer_id,
        public int $property_id,
        public string $status,
        public ?string $key_information,
        public ?array $note,
        public ?bool $is_fixed,
        public string $start_at,
        public string $end_at,
        public int $quarters,
    ) {
    }
}
