<?php

namespace App\DTOs\Deviation;

use App\DTOs\BaseData;
use App\DTOs\Schedule\ScheduleResponseDTO;
use App\Models\ScheduleDeviation;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Lazy;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapInputName(SnakeCaseMapper::class)]
class ScheduleDeviationResponseDTO extends BaseData
{
    public function __construct(
        public Lazy|null|int $id,
        public Lazy|null|int $scheduleId,
        public Lazy|null|array $types,
        public Lazy|null|bool $isHandled,
        public Lazy|null|DeviationMetaResponseDTO $meta,
        public Lazy|null|string $createdAt,
        public Lazy|null|string $updatedAt,
        public Lazy|null|ScheduleResponseDTO $schedule,
    ) {
    }

    public static function fromModel(ScheduleDeviation $deviation): self
    {
        return new self(
            Lazy::create(fn () => $deviation->id)->defaultIncluded(),
            Lazy::create(fn () => $deviation->schedule_id)->defaultIncluded(),
            Lazy::create(fn () => $deviation->types)->defaultIncluded(),
            Lazy::create(fn () => $deviation->is_handled)->defaultIncluded(),
            Lazy::create(fn () => $deviation->meta ? DeviationMetaResponseDTO::from($deviation->meta) : null)
                ->defaultIncluded(),
            Lazy::create(fn () => $deviation->created_at)->defaultIncluded(),
            Lazy::create(fn () => $deviation->updated_at)->defaultIncluded(),
            Lazy::create(fn () => ScheduleResponseDTO::from($deviation->schedule)),
        );
    }
}
