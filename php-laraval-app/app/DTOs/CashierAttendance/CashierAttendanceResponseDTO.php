<?php

namespace App\DTOs\CashierAttendance;

use App\DTOs\BaseData;
use App\DTOs\Store\StoreResponseDTO;
use App\DTOs\User\UserResponseDTO;
use App\DTOs\WorkHour\WorkHourResponseDTO;
use App\Models\CashierAttendance;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Lazy;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapInputName(SnakeCaseMapper::class)]
class CashierAttendanceResponseDTO extends BaseData
{
    public function __construct(
        public Lazy|null|int $id,
        public Lazy|null|int $userId,
        public Lazy|null|int $storeId,
        public Lazy|null|int $workHourId,
        public Lazy|null|int $checkInCauserId,
        public Lazy|null|int $checkOutCauserId,
        public Lazy|null|string $checkInAt,
        public Lazy|null|string $checkOutAt,
        public Lazy|null|string $createdAt,
        public Lazy|null|string $updatedAt,
        public Lazy|null|string $deletedAt,
        public Lazy|null|float $totalHours,
        public Lazy|null|UserResponseDTO $user,
        public Lazy|null|StoreResponseDTO $store,
        public Lazy|null|WorkHourResponseDTO $workHour,
        public Lazy|null|UserResponseDTO $checkInCauser,
        public Lazy|null|UserResponseDTO $checkOutCauser,
    ) {
    }

    public static function fromModel(CashierAttendance $attendance): self
    {
        return new self(
            Lazy::create(fn () => $attendance->id)->defaultIncluded(),
            Lazy::create(fn () => $attendance->user_id)->defaultIncluded(),
            Lazy::create(fn () => $attendance->store_id)->defaultIncluded(),
            Lazy::create(fn () => $attendance->work_hour_id)->defaultIncluded(),
            Lazy::create(fn () => $attendance->check_in_causer_id)->defaultIncluded(),
            Lazy::create(fn () => $attendance->check_out_causer_id)->defaultIncluded(),
            Lazy::create(fn () => $attendance->check_in_at)->defaultIncluded(),
            Lazy::create(fn () => $attendance->check_out_at)->defaultIncluded(),
            Lazy::create(fn () => $attendance->created_at)->defaultIncluded(),
            Lazy::create(fn () => $attendance->updated_at)->defaultIncluded(),
            Lazy::create(fn () => $attendance->deleted_at)->defaultIncluded(),
            Lazy::create(fn () => $attendance->total_hours)->defaultIncluded(),
            Lazy::create(fn () => UserResponseDTO::from($attendance->user)),
            Lazy::create(fn () => StoreResponseDTO::from($attendance->store)),
            Lazy::create(fn () => WorkHourResponseDTO::from($attendance->workHour)),
            Lazy::create(fn () => $attendance->checkInCauser ?
                UserResponseDTO::from($attendance->checkInCauser) : null),
            Lazy::create(fn () => $attendance->checkOutCauser ?
                UserResponseDTO::from($attendance->checkOutCauser) : null),
        );
    }
}
