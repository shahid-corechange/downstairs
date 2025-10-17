<?php

namespace App\DTOs\FixedPrice;

use App\DTOs\BaseData;
use App\DTOs\FixedPriceRow\OrderFixedPriceRowResponseDTO;
use App\Models\OrderFixedPrice;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\Lazy;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapInputName(SnakeCaseMapper::class)]
class OrderFixedPriceResponseDTO extends BaseData
{
    public function __construct(
        public Lazy|null|int $id,
        public Lazy|null|int $fixedPriceId,
        public Lazy|null|bool $isPerOrder,
        public Lazy|null|string $createdAt,
        public Lazy|null|string $updatedAt,
        public Lazy|null|string $deletedAt,
        public Lazy|null|FixedPriceResponseDTO $fixedPrice,
        #[DataCollectionOf(OrderFixedPriceRowResponseDTO::class)]
        public Lazy|null|DataCollection $rows,
        public Lazy|null|array $meta,
    ) {
    }

    public static function fromModel(OrderFixedPrice $fixedPrice): self
    {
        return new self(
            Lazy::create(fn () => $fixedPrice->id)->defaultIncluded(),
            Lazy::create(fn () => $fixedPrice->fixed_price_id)->defaultIncluded(),
            Lazy::create(fn () => $fixedPrice->is_per_order)->defaultIncluded(),
            Lazy::create(fn () => $fixedPrice->created_at)->defaultIncluded(),
            Lazy::create(fn () => $fixedPrice->updated_at)->defaultIncluded(),
            Lazy::create(fn () => $fixedPrice->deleted_at)->defaultIncluded(),
            Lazy::create(fn () => $fixedPrice->fixedPrice ?
                FixedPriceResponseDTO::from($fixedPrice->fixedPrice) :
                null)->defaultIncluded(),
            Lazy::create(fn () => OrderFixedPriceRowResponseDTO::collection($fixedPrice->rows)),
            Lazy::create(fn () => static::getModelMeta($fixedPrice))->defaultIncluded(),
        );
    }
}
