<?php

namespace App\DTOs\ScheduleCleaning;

use App\DTOs\BaseData;
use App\Models\ScheduleCleaning;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Lazy;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapInputName(SnakeCaseMapper::class)]
class ScheduleCleaningResponseDTO extends BaseData
{
    public function __construct(
        public Lazy|null|int $id,
        public Lazy|null|int $laundryOrderId,
        public Lazy|null|string $laundryType,
        public Lazy|null|string $createdAt,
        public Lazy|null|string $updatedAt,
    ) {
    }

    public static function fromModel(ScheduleCleaning $scheduleCleaning): self
    {
        return new self(
            Lazy::create(fn () => $scheduleCleaning->id)->defaultIncluded(),
            Lazy::create(fn () => $scheduleCleaning->laundry_order_id)->defaultIncluded(),
            Lazy::create(fn () => $scheduleCleaning->laundry_type)->defaultIncluded(),
            Lazy::create(fn () => $scheduleCleaning->created_at)->defaultIncluded(),
            Lazy::create(fn () => $scheduleCleaning->updated_at)->defaultIncluded(),
        );
    }
}
