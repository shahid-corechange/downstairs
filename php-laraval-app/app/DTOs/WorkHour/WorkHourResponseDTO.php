<?php

namespace App\DTOs\WorkHour;

use App\DTOs\BaseData;
use App\DTOs\CashierAttendance\CashierAttendanceResponseDTO;
use App\DTOs\ScheduleEmployee\ScheduleEmployeeResponseDTO;
use App\DTOs\User\UserResponseDTO;
use App\Models\WorkHour;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\Lazy;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapInputName(SnakeCaseMapper::class)]
class WorkHourResponseDTO extends BaseData
{
    public function __construct(
        public Lazy|null|int $id,
        public Lazy|null|int $userId,
        public Lazy|null|int $fortnoxAttendanceId,
        public Lazy|null|string $type,
        public Lazy|null|string $date,
        public Lazy|null|string $startTime,
        public Lazy|null|string $endTime,
        public Lazy|null|float $workHours,
        public Lazy|null|float $timeAdjustmentHours,
        public Lazy|null|float $totalHours,
        public Lazy|null|float $unapprovedHours,
        public Lazy|null|float $bookingHours,
        public Lazy|null|bool $hasDeviation,
        public Lazy|null|string $createdAt,
        public Lazy|null|string $updatedAt,
        public Lazy|null|UserResponseDTO $user,
        #[DataCollectionOf(ScheduleEmployeeResponseDTO::class)]
        public Lazy|null|DataCollection $schedules,
        #[DataCollectionOf(CashierAttendanceResponseDTO::class)]
        public Lazy|null|DataCollection $attendances,
    ) {
    }

    public static function fromModel(WorkHour $workHour): self
    {
        return new self(
            Lazy::create(fn () => $workHour->id)->defaultIncluded(),
            Lazy::create(fn () => $workHour->user_id)->defaultIncluded(),
            Lazy::create(fn () => $workHour->fortnox_attendance_id)->defaultIncluded(),
            Lazy::create(fn () => $workHour->type)->defaultIncluded(),
            Lazy::create(fn () => $workHour->date)->defaultIncluded(),
            Lazy::create(fn () => $workHour->start_time)->defaultIncluded(),
            Lazy::create(fn () => $workHour->end_time)->defaultIncluded(),
            Lazy::create(fn () => $workHour->work_hours)->defaultIncluded(),
            Lazy::create(fn () => $workHour->time_adjustment_hours)->defaultIncluded(),
            Lazy::create(fn () => $workHour->total_hours)->defaultIncluded(),
            Lazy::create(fn () => $workHour->unapproved_hours)->defaultIncluded(),
            Lazy::create(fn () => $workHour->booking_hours)->defaultIncluded(),
            Lazy::create(fn () => $workHour->has_deviation)->defaultIncluded(),
            Lazy::create(fn () => $workHour->created_at)->defaultIncluded(),
            Lazy::create(fn () => $workHour->updated_at)->defaultIncluded(),
            Lazy::create(fn () => UserResponseDTO::from($workHour->user)),
            Lazy::create(fn () => ScheduleEmployeeResponseDTO::collection($workHour->schedules)),
            Lazy::create(fn () => CashierAttendanceResponseDTO::collection($workHour->attendances)),
        );
    }
}
