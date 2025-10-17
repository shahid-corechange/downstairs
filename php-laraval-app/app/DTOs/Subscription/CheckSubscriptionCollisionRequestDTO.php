<?php

namespace App\DTOs\Subscription;

use App\DTOs\BaseData;
use App\Enums\Subscription\SubscriptionFrequencyEnum;
use App\Enums\Subscription\SubscriptionWeekdayEnum;
use Illuminate\Validation\Rule;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Mappers\CamelCaseMapper;

#[MapInputName(CamelCaseMapper::class)]
class CheckSubscriptionCollisionRequestDTO extends BaseData
{
    public function __construct(
        public int $team_id,
        public int $frequency,
        public string $start_at,
        public string $start_time_at,
        public string $end_time_at,
        public ?string $end_at = null,
        public string $response = 'boolean',
    ) {
    }

    public static function rules(): array
    {
        return [
            'team_id' => 'required|numeric|exists:teams,id',
            'frequency' => ['required', Rule::in(SubscriptionFrequencyEnum::values())],
            'weekday' => ['required', Rule::in(SubscriptionWeekdayEnum::values())],
            'start_at' => 'required|date_format:Y-m-d',
            'start_time_at' => 'required|date_format:H:i:s',
            'end_time_at' => 'required|date_format:H:i:s|after:start_time_at',
            'end_at' => 'nullable|date_format:Y-m-d|after:start_at',
            'response' => 'in:boolean,array',
        ];
    }
}
