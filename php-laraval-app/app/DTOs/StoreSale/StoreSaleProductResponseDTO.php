<?php

namespace App\DTOs\StoreSale;

use App\DTOs\BaseData;
use App\DTOs\Product\ProductResponseDTO;
use App\Models\StoreSaleProduct;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Lazy;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapInputName(SnakeCaseMapper::class)]
class StoreSaleProductResponseDTO extends BaseData
{
    public function __construct(
        public Lazy|null|int $id,
        public Lazy|null|int $storeSaleId,
        public Lazy|null|int $productId,
        public Lazy|null|string $name,
        public Lazy|null|string $note,
        public Lazy|null|int $quantity,
        public Lazy|null|float $price,
        public Lazy|null|int $vatGroup,
        public Lazy|null|float $discount,
        public Lazy|null|float $priceWithVat,
        public Lazy|null|float $discountAmount,
        public Lazy|null|float $vatAmount,
        public Lazy|null|float $priceWithDiscount,
        public Lazy|null|string $createdAt,
        public Lazy|null|string $updatedAt,
        public Lazy|null|StoreSaleResponseDTO $storeSale,
        public Lazy|null|ProductResponseDTO $product,
    ) {
    }

    public static function fromModel(StoreSaleProduct $storeSaleProduct): self
    {
        return new self(
            Lazy::create(fn () => $storeSaleProduct->id)->defaultIncluded(),
            Lazy::create(fn () => $storeSaleProduct->store_sale_id)->defaultIncluded(),
            Lazy::create(fn () => $storeSaleProduct->product_id)->defaultIncluded(),
            Lazy::create(fn () => $storeSaleProduct->name)->defaultIncluded(),
            Lazy::create(fn () => $storeSaleProduct->note)->defaultIncluded(),
            Lazy::create(fn () => $storeSaleProduct->quantity)->defaultIncluded(),
            Lazy::create(fn () => $storeSaleProduct->price)->defaultIncluded(),
            Lazy::create(fn () => $storeSaleProduct->vat_group)->defaultIncluded(),
            Lazy::create(fn () => $storeSaleProduct->discount)->defaultIncluded(),
            Lazy::create(fn () => $storeSaleProduct->price_with_vat)->defaultIncluded(),
            Lazy::create(fn () => $storeSaleProduct->discount_amount)->defaultIncluded(),
            Lazy::create(fn () => $storeSaleProduct->vat_amount)->defaultIncluded(),
            Lazy::create(fn () => $storeSaleProduct->price_with_discount)->defaultIncluded(),
            Lazy::create(fn () => $storeSaleProduct->created_at)->defaultIncluded(),
            Lazy::create(fn () => $storeSaleProduct->updated_at)->defaultIncluded(),
            Lazy::create(fn () => StoreSaleResponseDTO::from($storeSaleProduct->storeSale)),
            Lazy::create(fn () => ProductResponseDTO::from($storeSaleProduct->product)),
        );
    }
}
