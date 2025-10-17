<?php

namespace App\DTOs\Subscription;

use App\DTOs\BaseData;
use App\Enums\Subscription\SubscriptionFrequencyEnum;
use App\Rules\ValidFixedPrice;
use App\Rules\ValidStart;
use Illuminate\Validation\Rule;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\Mappers\CamelCaseMapper;
use Spatie\LaravelData\Optional;

#[MapInputName(CamelCaseMapper::class)]
class CompanySubscriptionWizardRequestDTO extends BaseData
{
    public function __construct(
        // plan
        public int $user_id,
        public int $customer_id,
        public int $service_id,
        #[DataCollectionOf(SubscriptionProductRequestDTO::class)]
        public DataCollection|Optional $products,
        public array|Optional $addon_ids,
        public string|null|Optional $description,
        public bool $is_fixed,
        public int $frequency,
        // date
        public string $start_at,
        public string|null|Optional $end_at,
        // fixed price
        public int|null|Optional $total_price,
        public int|null|Optional $fixed_price_id,
        // detail
        public SubscriptionCleaningWizardRequestDTO|Optional $cleaning_detail,
        public SubscriptionLaundryWizardRequestDTO|Optional $laundry_detail,
    ) {
    }

    public static function rules(): array
    {
        $cleaningDetail = request('cleaningDetail');
        $laundryDetail = request('laundryDetail');
        $startTime = $cleaningDetail ? $cleaningDetail['startTime'] : $laundryDetail['pickupTime'];
        $endValidation = request('frequency') !== 0 ? '|after:start_at' : '';

        return [
            // plan
            'user_id' => 'required|numeric|exists:users,id',
            'customer_id' => [
                'numeric',
                Rule::exists('customers', 'id')->where('membership_type', 'company'),
            ],
            'service_id' => [
                'required',
                'numeric',
                Rule::exists('services', 'id')->where('membership_type', 'company'),
            ],
            'addon_ids' => 'array',
            'addon_ids.*' => 'numeric|exists:addons,id|notIn:1', // Prevent adding laundry addon
            'description' => 'nullable|string',
            'is_fixed' => 'required|boolean',
            'frequency' => ['required', Rule::in(SubscriptionFrequencyEnum::values())],
            // date
            'start_at' => [
                'required',
                'date_format:Y-m-d',
                'after_or_equal:today',
                new ValidStart($startTime),
            ],
            'end_at' => 'nullable|date_format:Y-m-d'.$endValidation,
            // fixed price
            'total_price' => 'missing_with:fixed_price_id|nullable|numeric|gt:0',
            'fixed_price_id' => [
                'missing_with:total_price',
                'nullable',
                'numeric',
                new ValidFixedPrice(request('userId')),
            ],
        ];
    }
}
