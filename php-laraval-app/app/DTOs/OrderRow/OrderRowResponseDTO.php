<?php

namespace App\DTOs\OrderRow;

use App\DTOs\BaseData;
use App\Models\OrderRow;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Lazy;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapInputName(SnakeCaseMapper::class)]
class OrderRowResponseDTO extends BaseData
{
    public function __construct(
        public Lazy|null|int $id,
        public Lazy|null|int $orderId,
        public Lazy|null|string $fortnoxArticleId,
        public Lazy|null|string $description,
        public Lazy|null|string $unit,
        public Lazy|null|float $quantity,
        public Lazy|null|float $price,
        public Lazy|null|float $priceWithVat,
        public Lazy|null|int $discountPercentage,
        public Lazy|null|int $vat,
        public Lazy|null|bool $hasRut,
        public Lazy|null|bool $isServiceRow,
        public Lazy|null|bool $isMaterialRow,
        public Lazy|null|string $internalNote,
        public Lazy|null|string $createdAt,
        public Lazy|null|string $updatedAt,
    ) {
    }

    public static function fromModel(OrderRow $orderRow): self
    {
        return new self(
            Lazy::create(fn () => $orderRow->id)->defaultIncluded(),
            Lazy::create(fn () => $orderRow->order_id)->defaultIncluded(),
            Lazy::create(fn () => $orderRow->fortnox_article_id)->defaultIncluded(),
            Lazy::create(fn () => $orderRow->description)->defaultIncluded(),
            Lazy::create(fn () => $orderRow->unit)->defaultIncluded(),
            Lazy::create(fn () => $orderRow->quantity)->defaultIncluded(),
            Lazy::create(fn () => $orderRow->price)->defaultIncluded(),
            Lazy::create(fn () => $orderRow->price_with_vat)->defaultIncluded(),
            Lazy::create(fn () => $orderRow->discount_percentage)->defaultIncluded(),
            Lazy::create(fn () => $orderRow->vat)->defaultIncluded(),
            Lazy::create(fn () => $orderRow->has_rut)->defaultIncluded(),
            Lazy::create(fn () => $orderRow->is_service_row)->defaultIncluded(),
            Lazy::create(fn () => $orderRow->is_material_row)->defaultIncluded(),
            Lazy::create(fn () => $orderRow->internal_note)->defaultIncluded(),
            Lazy::create(fn () => $orderRow->created_at)->defaultIncluded(),
            Lazy::create(fn () => $orderRow->updated_at)->defaultIncluded(),
        );
    }
}
