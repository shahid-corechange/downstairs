<?php

namespace App\DTOs\Schedule;

use App\DTOs\BaseData;
use App\DTOs\User\UserResponseDTO;
use App\Models\ScheduleChangeRequest;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Lazy;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapInputName(SnakeCaseMapper::class)]
class ScheduleChangeResponseDTO extends BaseData
{
    public function __construct(
        public Lazy|null|int $id,
        public Lazy|null|int $scheduleId,
        public Lazy|null|int $causerId,
        public Lazy|null|string $originalStartAt,
        public Lazy|null|string $startAtChanged,
        public Lazy|null|string $originalEndAt,
        public Lazy|null|string $endAtChanged,
        public Lazy|null|bool $canReschedule,
        public Lazy|null|string $status,
        public Lazy|null|string $createdAt,
        public Lazy|null|string $updatedAt,
        public Lazy|null|string $deletedAt,
        public Lazy|null|ScheduleResponseDTO $schedule,
        public Lazy|null|UserResponseDTO $causer,
    ) {
    }

    public static function fromModel(ScheduleChangeRequest $changeRequest): self
    {
        return new self(
            Lazy::create(fn () => $changeRequest->id)->defaultIncluded(),
            Lazy::create(fn () => $changeRequest->schedule_id)->defaultIncluded(),
            Lazy::create(fn () => $changeRequest->causer_id)->defaultIncluded(),
            Lazy::create(fn () => $changeRequest->original_start_at)->defaultIncluded(),
            Lazy::create(fn () => $changeRequest->start_at_changed)->defaultIncluded(),
            Lazy::create(fn () => $changeRequest->original_end_at)->defaultIncluded(),
            Lazy::create(fn () => $changeRequest->end_at_changed)->defaultIncluded(),
            Lazy::create(fn () => $changeRequest->can_reschedule)->defaultIncluded(),
            Lazy::create(fn () => $changeRequest->status)->defaultIncluded(),
            Lazy::create(fn () => $changeRequest->created_at)->defaultIncluded(),
            Lazy::create(fn () => $changeRequest->updated_at)->defaultIncluded(),
            Lazy::create(fn () => $changeRequest->deleted_at)->defaultIncluded(),
            Lazy::create(fn () => ScheduleResponseDTO::from($changeRequest->schedule)),
            Lazy::create(
                fn () => $changeRequest->causer ? UserResponseDTO::from($changeRequest->causer) : null
            ),
        );
    }
}
