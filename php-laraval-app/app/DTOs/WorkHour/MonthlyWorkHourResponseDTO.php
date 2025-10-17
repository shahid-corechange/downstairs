<?php

namespace App\DTOs\WorkHour;

use App\DTOs\BaseData;
use App\DTOs\Employee\EmployeeResponseDTO;
use App\Models\MonthlyWorkHour;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Lazy;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapInputName(SnakeCaseMapper::class)]
class MonthlyWorkHourResponseDTO extends BaseData
{
    public function __construct(
        public Lazy|null|int $userId,
        public Lazy|null|string $fortnoxId,
        public Lazy|null|int $employeeId,
        public Lazy|null|string $fullname,
        public Lazy|null|int $month,
        public Lazy|null|int $year,
        public Lazy|null|float $scheduleWorkHours,
        public Lazy|null|float $storeWorkHours,
        public Lazy|null|float $totalWorkHours,
        public Lazy|null|float $adjustmentHours,
        public Lazy|null|float $totalHours,
        public Lazy|null|float $bookingHours,
        public Lazy|null|bool $hasDeviation,
        public Lazy|null|EmployeeResponseDTO $employee,
    ) {
    }

    public static function fromModel(MonthlyWorkHour $workHour): self
    {
        return new self(
            Lazy::create(fn () => $workHour->user_id)->defaultIncluded(),
            Lazy::create(fn () => $workHour->fortnox_id)->defaultIncluded(),
            Lazy::create(fn () => $workHour->employee_id)->defaultIncluded(),
            Lazy::create(fn () => $workHour->fullname)->defaultIncluded(),
            Lazy::create(fn () => $workHour->month)->defaultIncluded(),
            Lazy::create(fn () => $workHour->year)->defaultIncluded(),
            Lazy::create(fn () => $workHour->schedule_work_hours)->defaultIncluded(),
            Lazy::create(fn () => $workHour->store_work_hours)->defaultIncluded(),
            Lazy::create(fn () => $workHour->total_work_hours)->defaultIncluded(),
            Lazy::create(fn () => $workHour->adjustment_hours)->defaultIncluded(),
            Lazy::create(fn () => $workHour->total_hours)->defaultIncluded(),
            Lazy::create(fn () => $workHour->booking_hours)->defaultIncluded(),
            Lazy::create(fn () => $workHour->has_deviation)->defaultIncluded(),
            Lazy::create(fn () => EmployeeResponseDTO::from($workHour->employee)),
        );
    }
}
