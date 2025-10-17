<?php

namespace App\DTOs\LeaveRegistration;

use App\DTOs\BaseData;
use App\Models\LeaveRegistrationDetail;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Lazy;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapInputName(SnakeCaseMapper::class)]
class LeaveRegistrationDetailResponseDTO extends BaseData
{
    public function __construct(
        public Lazy|null|int $id,
        public Lazy|null|int $leaveRegistrationId,
        public Lazy|null|int $fortnoxAbsenceTransactionId,
        public Lazy|null|string $startAt,
        public Lazy|null|string $endAt,
        public Lazy|null|string $hours,
        public Lazy|null|string $createdAt,
        public Lazy|null|string $updatedAt,
        public Lazy|null|LeaveRegistrationResponseDTO $leaveRegistration,
    ) {
    }

    public static function fromModel(LeaveRegistrationDetail $detail): self
    {
        return new self(
            Lazy::create(fn () => $detail->id)->defaultIncluded(),
            Lazy::create(fn () => $detail->leave_registration_id)->defaultIncluded(),
            Lazy::create(fn () => $detail->fortnox_absence_transaction_id)->defaultIncluded(),
            Lazy::create(fn () => $detail->start_at)->defaultIncluded(),
            Lazy::create(fn () => $detail->end_at)->defaultIncluded(),
            Lazy::create(fn () => $detail->hours)->defaultIncluded(),
            Lazy::create(fn () => $detail->created_at)->defaultIncluded(),
            Lazy::create(fn () => $detail->updated_at)->defaultIncluded(),
            Lazy::create(fn () => LeaveRegistrationResponseDTO::from($detail->leaveRegistration)),
        );
    }
}
