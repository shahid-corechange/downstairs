<?php

namespace App\DTOs\FixedPriceRow;

use App\DTOs\BaseData;
use App\Models\OrderFixedPriceRow;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Lazy;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapInputName(SnakeCaseMapper::class)]
class OrderFixedPriceRowResponseDTO extends BaseData
{
    public function __construct(
        public Lazy|null|int $id,
        public Lazy|null|int $orderFixedPriceId,
        public Lazy|null|string $type,
        public Lazy|null|string $description,
        public Lazy|null|int $quantity,
        public Lazy|null|float $price,
        public Lazy|null|float $priceWithVat,
        public Lazy|null|int $vatGroup,
        public Lazy|null|bool $hasRut,
        public Lazy|null|string $createdAt,
        public Lazy|null|string $updatedAt,
    ) {
    }

    public static function fromModel(OrderFixedPriceRow $fixedPriceRow): self
    {
        return new self(
            Lazy::create(fn () => $fixedPriceRow->id)->defaultIncluded(),
            Lazy::create(fn () => $fixedPriceRow->order_fixed_price_id)->defaultIncluded(),
            Lazy::create(fn () => $fixedPriceRow->type)->defaultIncluded(),
            Lazy::create(fn () => $fixedPriceRow->description)->defaultIncluded(),
            Lazy::create(fn () => $fixedPriceRow->quantity)->defaultIncluded(),
            Lazy::create(fn () => $fixedPriceRow->price)->defaultIncluded(),
            Lazy::create(fn () => $fixedPriceRow->price_with_vat)->defaultIncluded(),
            Lazy::create(fn () => $fixedPriceRow->vat_group)->defaultIncluded(),
            Lazy::create(fn () => $fixedPriceRow->has_rut)->defaultIncluded(),
            Lazy::create(fn () => $fixedPriceRow->created_at)->defaultIncluded(),
            Lazy::create(fn () => $fixedPriceRow->updated_at)->defaultIncluded(),
        );
    }
}
