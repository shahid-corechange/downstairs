<?php

namespace App\DTOs\LeaveRegistration;

use App\DTOs\BaseData;
use App\DTOs\Employee\EmployeeResponseDTO;
use App\Models\LeaveRegistration;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\Lazy;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapInputName(SnakeCaseMapper::class)]
class LeaveRegistrationResponseDTO extends BaseData
{
    public function __construct(
        public Lazy|null|int $id,
        public Lazy|null|int $employeeId,
        public Lazy|null|string $type,
        public Lazy|null|string $startAt,
        public Lazy|null|string $endAt,
        public Lazy|null|bool $isStopped,
        public Lazy|null|bool $rescheduleNeeded,
        public Lazy|null|string $createdAt,
        public Lazy|null|string $updatedAt,
        public Lazy|null|string $deletedAt,
        public Lazy|null|EmployeeResponseDTO $employee,
        #[DataCollectionOf(LeaveRegistrationDetailResponseDTO::class)]
        public Lazy|null|DataCollection $details,
    ) {
    }

    public static function fromModel(LeaveRegistration $leaveRegistration): self
    {
        return new self(
            Lazy::create(fn () => $leaveRegistration->id)->defaultIncluded(),
            Lazy::create(fn () => $leaveRegistration->employee_id)->defaultIncluded(),
            Lazy::create(fn () => $leaveRegistration->type)->defaultIncluded(),
            Lazy::create(fn () => $leaveRegistration->start_at)->defaultIncluded(),
            Lazy::create(fn () => $leaveRegistration->end_at)->defaultIncluded(),
            Lazy::create(fn () => $leaveRegistration->is_stopped)->defaultIncluded(),
            Lazy::create(fn () => $leaveRegistration->reschedule_needed)->defaultIncluded(),
            Lazy::create(fn () => $leaveRegistration->created_at)->defaultIncluded(),
            Lazy::create(fn () => $leaveRegistration->updated_at)->defaultIncluded(),
            Lazy::create(fn () => $leaveRegistration->deleted_at)->defaultIncluded(),
            Lazy::create(fn () => EmployeeResponseDTO::from($leaveRegistration->employee)),
            Lazy::create(fn () => LeaveRegistrationDetailResponseDTO::collection($leaveRegistration->details)),
        );
    }
}
