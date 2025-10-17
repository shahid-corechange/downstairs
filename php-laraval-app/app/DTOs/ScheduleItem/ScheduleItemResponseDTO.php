<?php

namespace App\DTOs\ScheduleItem;

use App\DTOs\Addon\AddonResponseDTO;
use App\DTOs\BaseData;
use App\DTOs\Product\ProductResponseDTO;
use App\DTOs\Schedule\ScheduleResponseDTO;
use App\Models\Addon;
use App\Models\Product;
use App\Models\ScheduleItem;
use Spatie\LaravelData\Lazy;

class ScheduleItemResponseDTO extends BaseData
{
    public function __construct(
        public Lazy|null|int $id,
        public Lazy|null|int $scheduleId,
        public Lazy|null|int $itemableId,
        public Lazy|null|string $itemableType,
        public Lazy|null|float $price,
        public Lazy|null|float $quantity,
        public Lazy|null|int $discountPercentage,
        public Lazy|null|string $paymentMethod,
        public Lazy|null|string $createdAt,
        public Lazy|null|string $updatedAt,
        public Lazy|null|ScheduleResponseDTO $schedule,
        public Lazy|null|BaseData $item,
    ) {
    }

    public static function fromModel(ScheduleItem $scheduleItem): self
    {
        return new self(
            Lazy::create(fn () => $scheduleItem->id)->defaultIncluded(),
            Lazy::create(fn () => $scheduleItem->schedule_id)->defaultIncluded(),
            Lazy::create(fn () => $scheduleItem->itemable_id)->defaultIncluded(),
            Lazy::create(fn () => $scheduleItem->itemable_type)->defaultIncluded(),
            Lazy::create(fn () => $scheduleItem->price)->defaultIncluded(),
            Lazy::create(fn () => $scheduleItem->quantity)->defaultIncluded(),
            Lazy::create(fn () => $scheduleItem->discount_percentage)->defaultIncluded(),
            Lazy::create(fn () => $scheduleItem->payment_method)->defaultIncluded(),
            Lazy::create(fn () => $scheduleItem->created_at)->defaultIncluded(),
            Lazy::create(fn () => $scheduleItem->updated_at)->defaultIncluded(),
            Lazy::create(fn () => ScheduleResponseDTO::from($scheduleItem->schedule)),
            Lazy::create(fn () => self::getItem($scheduleItem)),
        );
    }

    private static function getItem(ScheduleItem $scheduleItem): BaseData
    {
        return match ($scheduleItem->itemable_type) {
            Product::class => ProductResponseDTO::from($scheduleItem->itemable),
            Addon::class => AddonResponseDTO::from($scheduleItem->itemable),
        };
    }
}
