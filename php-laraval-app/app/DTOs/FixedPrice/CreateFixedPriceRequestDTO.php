<?php

namespace App\DTOs\FixedPrice;

use App\DTOs\BaseData;
use App\DTOs\FixedPriceRow\CreateFixedPriceRowRequestDTO;
use App\Enums\FixedPrice\FixedPriceTypeEnum;
use Carbon\Carbon;
use Illuminate\Validation\Rule;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\Mappers\CamelCaseMapper;
use Spatie\LaravelData\Optional;

#[MapInputName(CamelCaseMapper::class)]
class CreateFixedPriceRequestDTO extends BaseData
{
    public function __construct(
        public int $user_id,
        public string $type,
        public Carbon|null|Optional $start_date,
        public Carbon|null|Optional $end_date,
        public bool $is_per_order,
        public ?array $subscription_ids,
        public ?array $laundry_product_ids,
        #[DataCollectionOf(CreateFixedPriceRowRequestDTO::class)]
        public DataCollection $rows,
    ) {
    }

    public static function rules(): array
    {
        $subscriptionIdsRules = 'array';

        if (request('type') !== FixedPriceTypeEnum::Laundry()) {
            $subscriptionIdsRules = $subscriptionIdsRules.'|required|min:1';
        }

        return [
            'user_id' => 'required|numeric|exists:users,id',
            'type' => ['required', Rule::in(FixedPriceTypeEnum::values())],
            'start_date' => 'nullable|date|date_format:Y-m-d|after_or_equal:today',
            'end_date' => 'nullable|date|date_format:Y-m-d|after:start_date',
            'subscription_ids' => $subscriptionIdsRules,
            'subscription_ids.*' => 'numeric|exists:subscriptions,id',
            'laundry_product_ids' => 'nullable|array',
            'laundry_product_ids.*' => 'numeric|exists:products,id',
        ];
    }
}
