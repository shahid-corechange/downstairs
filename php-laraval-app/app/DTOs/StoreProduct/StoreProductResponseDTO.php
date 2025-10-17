<?php

namespace App\DTOs\StoreProduct;

use App\DTOs\BaseData;
use App\DTOs\Product\ProductResponseDTO;
use App\DTOs\Store\StoreResponseDTO;
use App\Models\StoreProduct;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Lazy;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapInputName(SnakeCaseMapper::class)]
class StoreProductResponseDTO extends BaseData
{
    public function __construct(
        public Lazy|null|int $storeId,
        public Lazy|null|int $productId,
        public Lazy|null|string $status,
        public Lazy|null|StoreResponseDTO $store,
        public Lazy|null|ProductResponseDTO $product,
    ) {
    }

    public static function fromModel(StoreProduct $storeProduct): self
    {
        return new self(
            Lazy::create(fn () => $storeProduct->store_id)->defaultIncluded(),
            Lazy::create(fn () => $storeProduct->product_id)->defaultIncluded(),
            Lazy::create(fn () => $storeProduct->status)->defaultIncluded(),
            Lazy::create(fn () => StoreResponseDTO::from($storeProduct->store)),
            Lazy::create(fn () => ProductResponseDTO::from($storeProduct->product)),
        );
    }
}
