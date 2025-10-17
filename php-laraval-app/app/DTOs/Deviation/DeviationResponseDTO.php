<?php

namespace App\DTOs\Deviation;

use App\DTOs\BaseData;
use App\DTOs\Schedule\ScheduleResponseDTO;
use App\DTOs\User\UserResponseDTO;
use App\Models\Deviation;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Lazy;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapInputName(SnakeCaseMapper::class)]
class DeviationResponseDTO extends BaseData
{
    public function __construct(
        public Lazy|null|int $id,
        public Lazy|null|int $userId,
        public Lazy|null|int $scheduleId,
        public Lazy|null|string $type,
        public Lazy|null|string $reason,
        public Lazy|null|bool $isHandled,
        public Lazy|null|string $createdAt,
        public Lazy|null|string $updatedAt,
        public Lazy|null|UserResponseDTO $user,
        public Lazy|null|ScheduleResponseDTO $schedule,
    ) {
    }

    public static function fromModel(Deviation $deviation): self
    {
        return new self(
            Lazy::create(fn () => $deviation->id)->defaultIncluded(),
            Lazy::create(fn () => $deviation->user_id)->defaultIncluded(),
            Lazy::create(fn () => $deviation->schedule_id)->defaultIncluded(),
            Lazy::create(fn () => $deviation->type)->defaultIncluded(),
            Lazy::create(fn () => $deviation->reason)->defaultIncluded(),
            Lazy::create(fn () => $deviation->is_handled)->defaultIncluded(),
            Lazy::create(fn () => $deviation->created_at)->defaultIncluded(),
            Lazy::create(fn () => $deviation->updated_at)->defaultIncluded(),
            Lazy::create(fn () => UserResponseDTO::from($deviation->user)),
            Lazy::create(fn () => ScheduleResponseDTO::from($deviation->schedule)),
        );
    }
}
