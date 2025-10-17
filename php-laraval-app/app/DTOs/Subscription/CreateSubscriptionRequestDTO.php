<?php

namespace App\DTOs\Subscription;

use App\DTOs\BaseData;
use App\Enums\PermissionsEnum;
use App\Enums\Subscription\SubscriptionFrequencyEnum;
use App\Enums\Subscription\SubscriptionPlanEnum;
use App\Enums\Subscription\SubscriptionTypeEnum;
use App\Enums\Subscription\SubscriptionWeekdayEnum;
use Auth;
use Illuminate\Validation\Rule;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Mappers\CamelCaseMapper;
use Spatie\LaravelData\Optional;

#[MapInputName(CamelCaseMapper::class)]
class CreateSubscriptionRequestDTO extends BaseData
{
    public function __construct(
        public int|Optional $user_id,
        public int $customer_id,
        public int|Optional $property_id,
        public int|Optional $team_id,
        public string $type,
        public string $plan,
        public int $frequency,
        public int $weekday,
        public string $start_at,
        public string $end_at,
        public string $start_time_at,
        public string $end_time_at,
        public int $quarters,
        public int|Optional $refill_sequence,
        public string|Optional $description,
    ) {
    }

    public static function rules(): array
    {
        $isUserRequired = Auth::user()->can(PermissionsEnum::AccessCustomerApp()) ? 'nullable' : 'required';

        return [
            'user_id' => $isUserRequired.'|numeric|exists:users,id',
            'customer_id' => 'required|numeric|exists:customers,id',
            'property_id' => 'numeric|exists:properties,id',
            'team_id' => 'numeric|exists:teams,id',
            'type' => ['required', Rule::in(SubscriptionTypeEnum::values())],
            'plan' => ['required', Rule::in(SubscriptionPlanEnum::values())],
            'frequency' => ['required', Rule::in(SubscriptionFrequencyEnum::values())],
            'weekday' => ['required', Rule::in(SubscriptionWeekdayEnum::values())],
            'start_at' => 'required|string',
            'end_at' => 'required|string',
            'start_time_at' => 'required|string',
            'end_time_at' => 'required|string',
            'quarters' => 'required|numeric|min:1',
            'refill_sequence' => 'numeric|min:1',
            'description' => 'string',
        ];
    }
}
