<?php

namespace App\DTOs\UnassignSubscription;

use App\DTOs\BaseData;
use App\DTOs\Subscription\SubscriptionProductRequestDTO;
use App\Enums\MembershipTypeEnum;
use App\Enums\Subscription\SubscriptionFrequencyEnum;
use App\Rules\ValidStart;
use Illuminate\Validation\Rule;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\Mappers\CamelCaseMapper;
use Spatie\LaravelData\Optional;

#[MapInputName(CamelCaseMapper::class)]
class UpdateUnassignSubscriptionRequestDTO extends BaseData
{
    public function __construct(
        // plan
        public string $type,
        public int|Optional $user_id,
        public int|Optional $customer_id,
        public int|Optional $service_id,
        #[DataCollectionOf(SubscriptionProductRequestDTO::class)]
        public DataCollection|Optional $product_carts,
        public array|Optional $addon_ids,
        public string|null|Optional $description,
        public bool|Optional $is_fixed,
        public int|Optional $frequency,
        //
        public string|Optional $start_at,
        public string|null|Optional $end_at,
        // fixed price
        public float|null|Optional $fixed_price,
        // detail
        public UnassignSubscriptionCleaningRequestDTO|Optional $cleaning_detail,
        public UnassignSubscriptionLaundryRequestDTO|Optional $laundry_detail,
    ) {
    }

    public static function rules(): array
    {
        $cleaningDetail = request('cleaningDetail');
        $laundryDetail = request('laundryDetail');
        $startTime = $cleaningDetail ? $cleaningDetail['startTime'] : $laundryDetail['pickupTime'];
        $endValidation = request('frequency') !== 0 ? '|after:start_at' : '';
        $type = request('type');

        return [
            // plan
            'type' => ['required', 'string', Rule::in(MembershipTypeEnum::values())],
            'user_id' => 'numeric|exists:users,id',
            'customer_id' => [
                'numeric',
                Rule::exists('customers', 'id')->where('membership_type', $type),
            ],
            'service_id' => [
                'numeric',
                Rule::exists('services', 'id')->where('membership_type', $type),
            ],
            'addon_ids' => 'array',
            'addon_ids.*' => 'numeric|exists:addons,id|notIn:1', // Prevent adding laundry addon
            'description' => 'nullable|string',
            'is_fixed' => 'boolean',
            'frequency' => [Rule::in(SubscriptionFrequencyEnum::values())],
            // date
            'start_at' => [
                'date_format:Y-m-d',
                'after_or_equal:today',
                new ValidStart($startTime),
            ],
            'end_at' => 'nullable|date_format:Y-m-d|'.$endValidation,
            // fixed price
            'fixed_price' => 'numeric|gt:0',
        ];
    }
}
