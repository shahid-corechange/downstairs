<?php

namespace App\DTOs\ScheduleCleaningProduct;

use App\DTOs\BaseData;
use App\DTOs\Product\ProductResponseDTO;
use App\DTOs\ScheduleCleaning\ScheduleCleaningResponseDTO;
use App\Models\ScheduleCleaningProduct;
use Spatie\LaravelData\Lazy;

class ScheduleCleaningProductResponseDTO extends BaseData
{
    public function __construct(
        public Lazy|null|int $id,
        public Lazy|null|int $scheduleId,
        public Lazy|null|int $productId,
        public Lazy|null|string $name,
        public Lazy|null|string $description,
        public Lazy|null|float $price,
        public Lazy|null|float $quantity,
        public Lazy|null|int $discountPercentage,
        public Lazy|null|string $paymentMethod,
        public Lazy|null|string $createdAt,
        public Lazy|null|string $updatedAt,
        public Lazy|null|string $deletedAt,
        public Lazy|null|ScheduleCleaningResponseDTO $schedule,
        public Lazy|null|ProductResponseDTO $product,
    ) {
    }

    public static function fromModel(ScheduleCleaningProduct $scheduleCleaningProduct): self
    {
        return new self(
            Lazy::create(fn () => $scheduleCleaningProduct->id)->defaultIncluded(),
            Lazy::create(fn () => $scheduleCleaningProduct->schedule_cleaning_id)->defaultIncluded(),
            Lazy::create(fn () => $scheduleCleaningProduct->product_id)->defaultIncluded(),
            Lazy::create(fn () => $scheduleCleaningProduct->name)->defaultIncluded(),
            Lazy::create(fn () => $scheduleCleaningProduct->description)->defaultIncluded(),
            Lazy::create(fn () => $scheduleCleaningProduct->price)->defaultIncluded(),
            Lazy::create(fn () => $scheduleCleaningProduct->quantity)->defaultIncluded(),
            Lazy::create(fn () => $scheduleCleaningProduct->discount_percentage)->defaultIncluded(),
            Lazy::create(fn () => $scheduleCleaningProduct->payment_method)->defaultIncluded(),
            Lazy::create(fn () => $scheduleCleaningProduct->created_at)->defaultIncluded(),
            Lazy::create(fn () => $scheduleCleaningProduct->updated_at)->defaultIncluded(),
            Lazy::create(fn () => $scheduleCleaningProduct->deleted_at)->defaultIncluded(),
            Lazy::create(fn () => ScheduleCleaningResponseDTO::from($scheduleCleaningProduct->schedule)),
            Lazy::create(fn () => ProductResponseDTO::from($scheduleCleaningProduct->product)),
        );
    }
}
