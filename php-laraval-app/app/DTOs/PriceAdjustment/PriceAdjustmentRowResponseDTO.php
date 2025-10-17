<?php

namespace App\DTOs\PriceAdjustment;

use App\DTOs\Addon\AddonResponseDTO;
use App\DTOs\BaseData;
use App\DTOs\FixedPrice\FixedPriceResponseDTO;
use App\DTOs\Product\ProductResponseDTO;
use App\DTOs\Service\ServiceResponseDTO;
use App\Models\Addon;
use App\Models\FixedPrice;
use App\Models\PriceAdjustmentRow;
use App\Models\Product;
use App\Models\Service;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Lazy;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapInputName(SnakeCaseMapper::class)]
class PriceAdjustmentRowResponseDTO extends BaseData
{
    public function __construct(
        public Lazy|null|int $id,
        public Lazy|null|int $adjustableId,
        public Lazy|null|string $adjustableType,
        public Lazy|null|string $adjustableName,
        public Lazy|null|int $priceAdjustmentId,
        public Lazy|null|float $previousPrice,
        public Lazy|null|float $price,
        public Lazy|null|float $vatGroup,
        public Lazy|null|float $previousPriceWithVat,
        public Lazy|null|float $priceWithVat,
        public Lazy|null|string $status,
        public Lazy|null|string $createdAt,
        public Lazy|null|string $updatedAt,
        public Lazy|null|BaseData $adjustable,
        public Lazy|null|PriceAdjustmentResponseDTO $priceAdjustment,
    ) {
    }

    public static function fromModel(PriceAdjustmentRow $row): self
    {
        return new self(
            Lazy::create(fn () => $row->id)->defaultIncluded(),
            Lazy::create(fn () => $row->adjustable_id)->defaultIncluded(),
            Lazy::create(fn () => $row->adjustable_type)->defaultIncluded(),
            Lazy::create(fn () => $row->adjustable_name)->defaultIncluded(),
            Lazy::create(fn () => $row->price_adjustment_id)->defaultIncluded(),
            Lazy::create(fn () => $row->previous_price)->defaultIncluded(),
            Lazy::create(fn () => $row->price)->defaultIncluded(),
            Lazy::create(fn () => $row->vat_group)->defaultIncluded(),
            Lazy::create(fn () => $row->previous_price_with_vat)->defaultIncluded(),
            Lazy::create(fn () => $row->price_with_vat)->defaultIncluded(),
            Lazy::create(fn () => $row->status)->defaultIncluded(),
            Lazy::create(fn () => $row->created_at)->defaultIncluded(),
            Lazy::create(fn () => $row->updated_at)->defaultIncluded(),
            Lazy::create(fn () => self::getAdjustable($row)),
            Lazy::create(fn () => PriceAdjustmentResponseDTO::from($row->priceAdjustment)),
        );
    }

    private static function getAdjustable(PriceAdjustmentRow $row): ?BaseData
    {
        if ($row->adjustable_type === Service::class) {
            return ServiceResponseDTO::from($row->adjustable);
        } elseif ($row->adjustable_type === Addon::class) {
            return AddonResponseDTO::from($row->adjustable);
        } elseif ($row->adjustable_type === Product::class) {
            return ProductResponseDTO::from($row->adjustable);
        } elseif ($row->adjustable_type === FixedPrice::class) {
            return FixedPriceResponseDTO::from($row->adjustable);
        }

        return null;
    }
}
