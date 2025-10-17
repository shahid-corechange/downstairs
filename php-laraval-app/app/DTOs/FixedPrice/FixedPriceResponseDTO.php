<?php

namespace App\DTOs\FixedPrice;

use App\DTOs\BaseData;
use App\DTOs\FixedPriceRow\FixedPriceRowResponseDTO;
use App\DTOs\Product\ProductResponseDTO;
use App\DTOs\Subscription\SubscriptionResponseDTO;
use App\DTOs\User\UserResponseDTO;
use App\Models\FixedPrice;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\Lazy;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapInputName(SnakeCaseMapper::class)]
class FixedPriceResponseDTO extends BaseData
{
    public function __construct(
        public Lazy|null|int $id,
        public Lazy|null|int $userId,
        public Lazy|null|string $type,
        public Lazy|null|string $startDate,
        public Lazy|null|string $endDate,
        public Lazy|null|bool $isActive,
        public Lazy|null|bool $isPerOrder,
        public Lazy|null|bool $isIncludeLaundry,
        public Lazy|null|bool $hasActiveSubscriptions,
        public Lazy|null|string $createdAt,
        public Lazy|null|string $updatedAt,
        public Lazy|null|string $deletedAt,
        public Lazy|null|UserResponseDTO $user,
        #[DataCollectionOf(FixedPriceRowResponseDTO::class)]
        public Lazy|null|DataCollection $rows,
        #[DataCollectionOf(SubscriptionResponseDTO::class)]
        public Lazy|null|DataCollection $subscriptions,
        #[DataCollectionOf(ProductResponseDTO::class)]
        public Lazy|null|DataCollection $laundryProducts,
        public Lazy|null|array $meta,
    ) {
    }

    public static function fromModel(FixedPrice $fixedPrice): self
    {
        return new self(
            Lazy::create(fn () => $fixedPrice->id)->defaultIncluded(),
            Lazy::create(fn () => $fixedPrice->user_id)->defaultIncluded(),
            Lazy::create(fn () => $fixedPrice->type)->defaultIncluded(),
            Lazy::create(fn () => $fixedPrice->start_date)->defaultIncluded(),
            Lazy::create(fn () => $fixedPrice->end_date)->defaultIncluded(),
            Lazy::create(fn () => $fixedPrice->is_active)->defaultIncluded(),
            Lazy::create(fn () => $fixedPrice->is_per_order)->defaultIncluded(),
            Lazy::create(fn () => $fixedPrice->is_include_laundry)->defaultIncluded(),
            Lazy::create(fn () => $fixedPrice->has_active_subscriptions)->defaultIncluded(),
            Lazy::create(fn () => $fixedPrice->created_at)->defaultIncluded(),
            Lazy::create(fn () => $fixedPrice->updated_at)->defaultIncluded(),
            Lazy::create(fn () => $fixedPrice->deleted_at)->defaultIncluded(),
            Lazy::create(fn () => UserResponseDTO::from($fixedPrice->user)),
            Lazy::create(fn () => FixedPriceRowResponseDTO::collection($fixedPrice->rows)),
            Lazy::create(fn () => SubscriptionResponseDTO::collection($fixedPrice->subscriptions)),
            Lazy::create(fn () => ProductResponseDTO::collection($fixedPrice->laundryProducts)),
            Lazy::create(fn () => static::getModelMeta($fixedPrice))->defaultIncluded(),
        );
    }
}
