<?php

namespace App\DTOs\Subscription;

use App\DTOs\BaseData;
use App\DTOs\Product\ProductResponseDTO;
use App\Models\SubscriptionProduct;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Lazy;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapInputName(SnakeCaseMapper::class)]
class SubscriptionProductResponseDTO extends BaseData
{
    public function __construct(
        public Lazy|null|int $id,
        public Lazy|null|int $subscriptionId,
        public Lazy|null|int $productId,
        public Lazy|null|int $quantity,
        public Lazy|null|string $createdAt,
        public Lazy|null|string $updatedAt,
        public Lazy|null|SubscriptionResponseDTO $subscription,
        public Lazy|null|ProductResponseDTO $product,
    ) {
    }

    public static function fromModel(SubscriptionProduct $subscriptionProduct): self
    {
        return new self(
            Lazy::create(fn () => $subscriptionProduct->id)->defaultIncluded(),
            Lazy::create(fn () => $subscriptionProduct->subscription_id)->defaultIncluded(),
            Lazy::create(fn () => $subscriptionProduct->product_id)->defaultIncluded(),
            Lazy::create(fn () => $subscriptionProduct->quantity)->defaultIncluded(),
            Lazy::create(fn () => $subscriptionProduct->created_at)->defaultIncluded(),
            Lazy::create(fn () => $subscriptionProduct->updated_at)->defaultIncluded(),
            Lazy::create(fn () => SubscriptionResponseDTO::from($subscriptionProduct->subscription)),
            Lazy::create(fn () => ProductResponseDTO::from($subscriptionProduct->product)),
        );
    }
}
