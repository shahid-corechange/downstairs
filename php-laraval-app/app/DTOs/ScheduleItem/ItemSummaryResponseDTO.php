<?php

namespace App\DTOs\ScheduleItem;

use App\DTOs\BaseData;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Lazy;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapInputName(SnakeCaseMapper::class)]
class ItemSummaryResponseDTO extends BaseData
{
    public function __construct(
        public Lazy|null|int $id,
        public Lazy|null|int $scheduleId,
        public Lazy|null|int $itemableId,
        public Lazy|null|string $itemableType,
        public Lazy|null|string $name,
        public Lazy|null|float $price,
        public Lazy|null|float $quantity,
        public Lazy|null|int $discountPercentage,
        public Lazy|null|string $createdAt,
        public Lazy|null|string $updatedAt,
        public Lazy|null|bool $isCharge,
    ) {
    }
}
