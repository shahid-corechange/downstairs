<?php

namespace App\DTOs\ScheduleLaundry;

use App\DTOs\BaseData;
use App\DTOs\LaundryOrder\LaundryOrderResponseDTO;
use App\DTOs\Schedule\ScheduleResponseDTO;
use App\Models\ScheduleLaundry;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Lazy;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapInputName(SnakeCaseMapper::class)]
class ScheduleLaundryResponseDTO extends BaseData
{
    public function __construct(
        public Lazy|null|int $id,
        public Lazy|null|int $laundryOrderId,
        public Lazy|null|string $type,
        public Lazy|null|LaundryOrderResponseDTO $laundryOrder,
        public Lazy|null|ScheduleResponseDTO $schedule,
    ) {
    }

    public static function fromModel(ScheduleLaundry $scheduleLaundry): self
    {
        return new self(
            Lazy::create(fn () => $scheduleLaundry->id)->defaultIncluded(),
            Lazy::create(fn () => $scheduleLaundry->laundry_order_id)->defaultIncluded(),
            Lazy::create(fn () => $scheduleLaundry->type)->defaultIncluded(),
            Lazy::create(fn () => LaundryOrderResponseDTO::from($scheduleLaundry->laundryOrder)),
            Lazy::create(fn () => ScheduleResponseDTO::from($scheduleLaundry->schedule)),
        );
    }
}
