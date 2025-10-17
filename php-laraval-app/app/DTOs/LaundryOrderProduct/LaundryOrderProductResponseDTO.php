<?php

namespace App\DTOs\LaundryOrderProduct;

use App\DTOs\BaseData;
use App\DTOs\LaundryOrder\LaundryOrderResponseDTO;
use App\DTOs\Product\ProductResponseDTO;
use App\Models\LaundryOrderProduct;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Lazy;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapInputName(SnakeCaseMapper::class)]
class LaundryOrderProductResponseDTO extends BaseData
{
    public function __construct(
        public Lazy|null|int $id,
        public Lazy|null|int $laundryOrderId,
        public Lazy|null|int $productId,
        public Lazy|null|string $name,
        public Lazy|null|string $note,
        public Lazy|null|int $quantity,
        public Lazy|null|float $price,
        public Lazy|null|float $discount,
        public Lazy|null|int $vatGroup,
        public Lazy|null|bool $hasRut,
        public Lazy|null|float $priceWithVat,
        public Lazy|null|float $totalPriceWithVat,
        public Lazy|null|float $totalDiscountAmount,
        public Lazy|null|float $totalVatAmount,
        public Lazy|null|float $totalPriceWithDiscount,
        public Lazy|null|float $totalRut,
        public Lazy|null|string $createdAt,
        public Lazy|null|string $updatedAt,
        public Lazy|null|LaundryOrderResponseDTO $laundryOrder,
        public Lazy|null|ProductResponseDTO $product,
    ) {
    }

    public static function fromModel(LaundryOrderProduct $laundryOrderProduct): self
    {

        return new self(
            Lazy::create(fn () => $laundryOrderProduct->id)->defaultIncluded(),
            Lazy::create(fn () => $laundryOrderProduct->laundry_order_id)->defaultIncluded(),
            Lazy::create(fn () => $laundryOrderProduct->product_id)->defaultIncluded(),
            Lazy::create(fn () => $laundryOrderProduct->name)->defaultIncluded(),
            Lazy::create(fn () => $laundryOrderProduct->note)->defaultIncluded(),
            Lazy::create(fn () => $laundryOrderProduct->quantity)->defaultIncluded(),
            Lazy::create(fn () => $laundryOrderProduct->price)->defaultIncluded(),
            Lazy::create(fn () => $laundryOrderProduct->discount)->defaultIncluded(),
            Lazy::create(fn () => $laundryOrderProduct->vat_group)->defaultIncluded(),
            Lazy::create(fn () => $laundryOrderProduct->has_rut)->defaultIncluded(),
            Lazy::create(fn () => $laundryOrderProduct->price_with_vat)->defaultIncluded(),
            Lazy::create(fn () => $laundryOrderProduct->total_price_with_vat)->defaultIncluded(),
            Lazy::create(fn () => $laundryOrderProduct->total_discount_amount)->defaultIncluded(),
            Lazy::create(fn () => $laundryOrderProduct->total_vat_amount)->defaultIncluded(),
            Lazy::create(fn () => $laundryOrderProduct->total_price_with_discount)->defaultIncluded(),
            Lazy::create(fn () => $laundryOrderProduct->total_rut)->defaultIncluded(),
            Lazy::create(fn () => $laundryOrderProduct->created_at)->defaultIncluded(),
            Lazy::create(fn () => $laundryOrderProduct->updated_at)->defaultIncluded(),
            Lazy::create(fn () => LaundryOrderResponseDTO::from($laundryOrderProduct->laundryOrder)),
            Lazy::create(fn () => ProductResponseDTO::from($laundryOrderProduct->product)),
        );
    }
}
