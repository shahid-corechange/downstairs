<?php

namespace App\DTOs\TimeAdjustment;

use App\DTOs\BaseData;
use App\DTOs\ScheduleEmployee\ScheduleEmployeeResponseDTO;
use App\DTOs\User\UserResponseDTO;
use App\Models\TimeAdjustment;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Lazy;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapInputName(SnakeCaseMapper::class)]
class TimeAdjustmentResponseDTO extends BaseData
{
    public function __construct(
        public Lazy|null|int $id,
        public Lazy|null|int $scheduleEmployeeId,
        public Lazy|null|int $causerId,
        public Lazy|null|int $quarters,
        public Lazy|null|string $reason,
        public Lazy|null|string $createdAt,
        public Lazy|null|string $updatedAt,
        public Lazy|null|ScheduleEmployeeResponseDTO $schedule,
        public Lazy|null|UserResponseDTO $causer,
    ) {
    }

    public static function fromModel(TimeAdjustment $timeAdjustment): self
    {
        return new self(
            Lazy::create(fn () => $timeAdjustment->id)->defaultIncluded(),
            Lazy::create(fn () => $timeAdjustment->schedule_employee_id)->defaultIncluded(),
            Lazy::create(fn () => $timeAdjustment->causer_id)->defaultIncluded(),
            Lazy::create(fn () => $timeAdjustment->quarters)->defaultIncluded(),
            Lazy::create(fn () => $timeAdjustment->reason)->defaultIncluded(),
            Lazy::create(fn () => $timeAdjustment->created_at)->defaultIncluded(),
            Lazy::create(fn () => $timeAdjustment->updated_at)->defaultIncluded(),
            Lazy::create(fn () => ScheduleEmployeeResponseDTO::from($timeAdjustment->schedule)),
            Lazy::create(fn () => UserResponseDTO::from($timeAdjustment->causer)),
        );
    }
}
