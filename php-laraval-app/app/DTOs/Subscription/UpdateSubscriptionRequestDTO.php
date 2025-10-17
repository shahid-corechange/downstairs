<?php

namespace App\DTOs\Subscription;

use App\DTOs\BaseData;
use App\Enums\Subscription\SubscriptionFrequencyEnum;
use App\Rules\DateAfterNow;
use App\Rules\ValidStart;
use Illuminate\Validation\Rule;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\Mappers\CamelCaseMapper;
use Spatie\LaravelData\Optional;

#[MapInputName(CamelCaseMapper::class)]
class UpdateSubscriptionRequestDTO extends BaseData
{
    public function __construct(
        #[DataCollectionOf(SubscriptionProductRequestDTO::class)]
        public DataCollection|Optional $products,
        public array|Optional $addon_ids,
        public string|null|Optional $description,
        public bool|Optional $is_fixed,
        public int|Optional $frequency,
        public int|null|Optional $total_price,
        // date
        public string $start_at,
        public string|null|Optional $end_at,
        // detail
        public SubscriptionCleaningUpdateRequestDTO|null|Optional $cleaning_detail,
        public SubscriptionLaundryUpdateRequestDTO|null|Optional $laundry_detail,
    ) {
    }

    public static function rules(): array
    {
        /** @var Subscription $subscription */
        $subscription = request()->route('subscription');
        $startTime = self::getStartTime();

        return [
            'addon_ids' => 'array',
            'addon_ids.*' => 'numeric|exists:addons,id|notIn:1', // Prevent adding laundry addon
            'description' => 'nullable|string',
            'is_fixed' => 'boolean',
            'frequency' => Rule::in(SubscriptionFrequencyEnum::values()),
            'total_price' => 'numeric|gt:0',
            'start_at' => [
                'date_format:Y-m-d',
                new DateAfterNow($subscription->start_at),
                new ValidStart($startTime),
            ],
            'end_at' => 'nullable|date_format:Y-m-d|after:start_at',
        ];
    }

    private static function getStartTime()
    {
        /** @var Subscription $subscription */
        $subscription = request()->route('subscription');

        if ($subscription->isCleaning()) {
            $cleaningDetail = request('cleaningDetail');

            return $cleaningDetail['startTime'] ? $cleaningDetail['startTime'] :
                $subscription->subscribable->start_time;
        } else {
            $laundryDetail = request('laundryDetail');

            return $laundryDetail['pickupTime'] ? $laundryDetail['pickupTime'] :
                $subscription->subscribable->pickup_time;
        }
    }
}
