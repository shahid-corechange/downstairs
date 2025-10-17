<?php

namespace App\DTOs\FixedPrice;

use App\DTOs\BaseData;
use App\Rules\MetaProperty;
use App\Rules\MetaRule;
use Carbon\Carbon;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Mappers\CamelCaseMapper;
use Spatie\LaravelData\Optional;

#[MapInputName(CamelCaseMapper::class)]
class UpdateFixedPriceRequestDTO extends BaseData
{
    public function __construct(
        public Carbon|null|Optional $start_date,
        public Carbon|null|Optional $end_date,
        public bool $is_per_order,
        public ?array $subscription_ids,
    ) {
    }

    public static function rules(): array
    {
        return [
            'start_date' => 'nullable|date|date_format:Y-m-d|after_or_equal:today',
            'end_date' => 'nullable|date|date_format:Y-m-d|after:start_date',
            'is_per_order' => 'required|boolean',
            'subscription_ids' => 'array',
            'subscription_ids.*' => 'numeric|exists:subscriptions,id',
            'meta' => [new MetaRule()],
            'meta.*' => [new MetaProperty()],
        ];
    }
}
