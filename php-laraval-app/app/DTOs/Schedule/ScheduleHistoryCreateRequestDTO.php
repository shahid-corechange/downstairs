<?php

namespace App\DTOs\Schedule;

use App\DTOs\BaseData;
use App\DTOs\Subscription\SubscriptionProductRequestDTO;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\Mappers\CamelCaseMapper;
use Spatie\LaravelData\Optional;

#[MapInputName(CamelCaseMapper::class)]
class ScheduleHistoryCreateRequestDTO extends BaseData
{
    public function __construct(
        // plan
        public int $user_id,
        public int $property_id,
        public int $customer_id,
        public int $team_id,
        public int $service_id,
        #[DataCollectionOf(SubscriptionProductRequestDTO::class)]
        public DataCollection|Optional $products,
        public array|Optional $addon_ids,
        public string|null|Optional $description,
        public int $quarters,
        // time and frequency
        public string $start_at,
        public string $start_time_at,
        // fixed price
        public int|null|Optional $total_price,
        // workers
        #[DataCollectionOf(ScheduleHistoryWorkerRequestDTO::class)]
        public DataCollection|Optional $workers,
    ) {
    }

    public static function rules(): array
    {
        return [
            // plan
            'user_id' => 'required|numeric|exists:users,id',
            'property_id' => 'required|numeric|exists:properties,id',
            'customer_id' => 'required|numeric|exists:customers,id',
            'team_id' => 'required|numeric|exists:teams,id',
            'service_id' => 'required|numeric|exists:services,id',
            'addon_ids' => 'array',
            'addon_ids.*' => 'numeric|exists:addons,id|notIn:1', // Prevent adding laundry addon
            'description' => 'nullable|string',
            'quarters' => 'required|numeric|min:1',
            // time and frequency
            'start_at' => 'required|date_format:Y-m-d',
            'start_time_at' => 'required|date_format:H:i:s',
            // fixed price
            'total_price' => 'numeric|gt:0',
            // workers
            'workers' => 'array',
        ];
    }
}
