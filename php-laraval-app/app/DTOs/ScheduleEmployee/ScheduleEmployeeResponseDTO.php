<?php

namespace App\DTOs\ScheduleEmployee;

use App\DTOs\BaseData;
use App\DTOs\Schedule\ScheduleResponseDTO;
use App\DTOs\TimeAdjustment\TimeAdjustmentResponseDTO;
use App\DTOs\User\UserResponseDTO;
use App\Models\ScheduleEmployee;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Lazy;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapInputName(SnakeCaseMapper::class)]
class ScheduleEmployeeResponseDTO extends BaseData
{
    public function __construct(
        public Lazy|null|int $id,
        public Lazy|null|int $scheduleId,
        public Lazy|null|int $workHourId,
        public Lazy|null|string $userId,
        public Lazy|null|string $startLatitude,
        public Lazy|null|string $startLongitude,
        public Lazy|null|string $startIp,
        public Lazy|null|string $startAt,
        public Lazy|null|string $endLatitude,
        public Lazy|null|string $endLongitude,
        public Lazy|null|string $endIp,
        public Lazy|null|string $endAt,
        public Lazy|null|string $status,
        public Lazy|null|string $description,
        public Lazy|null|int $totalWorkTime,
        public Lazy|null|float $timeAdjustmentHours,
        public Lazy|null|string $createdAt,
        public Lazy|null|string $updatedAt,
        public Lazy|null|string $deletedAt,
        public Lazy|null|UserResponseDTO $user,
        public Lazy|null|TimeAdjustmentResponseDTO $timeAdjustment,
        public Lazy|null|ScheduleResponseDTO $schedule,
    ) {
    }

    public static function fromModel(ScheduleEmployee $scheduleEmployee): self
    {
        return new self(
            Lazy::create(fn () => $scheduleEmployee->id)->defaultIncluded(),
            Lazy::create(fn () => $scheduleEmployee->schedule_id)->defaultIncluded(),
            Lazy::create(fn () => $scheduleEmployee->work_hour_id)->defaultIncluded(),
            Lazy::create(fn () => $scheduleEmployee->user_id)->defaultIncluded(),
            Lazy::create(fn () => $scheduleEmployee->start_latitude)->defaultIncluded(),
            Lazy::create(fn () => $scheduleEmployee->start_longitude)->defaultIncluded(),
            Lazy::create(fn () => $scheduleEmployee->start_ip)->defaultIncluded(),
            Lazy::create(fn () => $scheduleEmployee->start_at)->defaultIncluded(),
            Lazy::create(fn () => $scheduleEmployee->end_latitude)->defaultIncluded(),
            Lazy::create(fn () => $scheduleEmployee->end_longitude)->defaultIncluded(),
            Lazy::create(fn () => $scheduleEmployee->end_ip)->defaultIncluded(),
            Lazy::create(fn () => $scheduleEmployee->end_at)->defaultIncluded(),
            Lazy::create(fn () => $scheduleEmployee->status)->defaultIncluded(),
            Lazy::create(fn () => $scheduleEmployee->description)->defaultIncluded(),
            Lazy::create(fn () => $scheduleEmployee->total_work_time)->defaultIncluded(),
            Lazy::create(fn () => $scheduleEmployee->time_adjustment_hours)->defaultIncluded(),
            Lazy::create(fn () => $scheduleEmployee->created_at)->defaultIncluded(),
            Lazy::create(fn () => $scheduleEmployee->updated_at)->defaultIncluded(),
            Lazy::create(fn () => $scheduleEmployee->deleted_at)->defaultIncluded(),
            Lazy::create(fn () => UserResponseDTO::from($scheduleEmployee->user)),
            Lazy::create(fn () => $scheduleEmployee->timeAdjustment ?
                TimeAdjustmentResponseDTO::from($scheduleEmployee->timeAdjustment) : null),
            Lazy::create(fn () => ScheduleResponseDTO::from($scheduleEmployee->schedule)),
        );
    }
}
