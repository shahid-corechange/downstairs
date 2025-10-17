<?php

namespace App\DTOs\PriceAdjustment;

use App\DTOs\BaseData;
use App\DTOs\User\UserResponseDTO;
use App\Models\PriceAdjustment;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\Lazy;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapInputName(SnakeCaseMapper::class)]
class PriceAdjustmentResponseDTO extends BaseData
{
    public function __construct(
        public Lazy|null|int $id,
        public Lazy|null|int $causerId,
        public Lazy|null|string $type,
        public Lazy|null|string $description,
        public Lazy|null|string $priceType,
        public Lazy|null|float $price,
        public Lazy|null|string $executionDate,
        public Lazy|null|string $status,
        public Lazy|null|string $createdAt,
        public Lazy|null|string $updatedAt,
        public Lazy|null|string $deletedAt,
        public Lazy|null|UserResponseDTO $causer,
        #[DataCollectionOf(PriceAdjustmentRowResponseDTO::class)]
        public Lazy|null|DataCollection $rows,
    ) {
    }

    public static function fromModel(PriceAdjustment $priceAdjustment): self
    {
        return new self(
            Lazy::create(fn () => $priceAdjustment->id)->defaultIncluded(),
            Lazy::create(fn () => $priceAdjustment->causer_id)->defaultIncluded(),
            Lazy::create(fn () => $priceAdjustment->type)->defaultIncluded(),
            Lazy::create(fn () => $priceAdjustment->description)->defaultIncluded(),
            Lazy::create(fn () => $priceAdjustment->price_type)->defaultIncluded(),
            Lazy::create(fn () => $priceAdjustment->price)->defaultIncluded(),
            Lazy::create(fn () => $priceAdjustment->execution_date)->defaultIncluded(),
            Lazy::create(fn () => $priceAdjustment->status)->defaultIncluded(),
            Lazy::create(fn () => $priceAdjustment->created_at)->defaultIncluded(),
            Lazy::create(fn () => $priceAdjustment->updated_at)->defaultIncluded(),
            Lazy::create(fn () => $priceAdjustment->deleted_at)->defaultIncluded(),
            Lazy::create(fn () => UserResponseDTO::from($priceAdjustment->causer)),
            Lazy::create(fn () => PriceAdjustmentRowResponseDTO::collection($priceAdjustment->rows)),
        );
    }
}
