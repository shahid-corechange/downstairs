<?php

namespace App\DTOs\Credit;

use App\DTOs\BaseData;
use App\DTOs\Schedule\ScheduleResponseDTO;
use App\DTOs\User\UserResponseDTO;
use App\Models\Credit;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\Lazy;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapInputName(SnakeCaseMapper::class)]
class CreditResponseDTO extends BaseData
{
    public function __construct(
        public Lazy|null|int $id,
        public Lazy|null|int $userId,
        public Lazy|null|int $scheduleId,
        public Lazy|null|int $issuerId,
        public Lazy|null|int $initialAmount,
        public Lazy|null|int $remainingAmount,
        public Lazy|null|string $type,
        public Lazy|null|string $description,
        public Lazy|null|string $validUntil,
        public Lazy|null|bool $isSystemCreated,
        public Lazy|null|string $createdAt,
        public Lazy|null|string $updatedAt,
        public Lazy|null|UserResponseDTO $user,
        public Lazy|null|ScheduleResponseDTO $schedule,
        public Lazy|null|UserResponseDTO $issuer,
        #[DataCollectionOf(CreditTransactionResponseDTO::class)]
        public Lazy|null|DataCollection $transactions,
    ) {
    }

    public static function fromModel(Credit $credit): self
    {
        return new self(
            Lazy::create(fn () => $credit->id)->defaultIncluded(),
            Lazy::create(fn () => $credit->user_id)->defaultIncluded(),
            Lazy::create(fn () => $credit->schedule_id)->defaultIncluded(),
            Lazy::create(fn () => $credit->issuer_id)->defaultIncluded(),
            Lazy::create(fn () => $credit->initial_amount)->defaultIncluded(),
            Lazy::create(fn () => $credit->remaining_amount)->defaultIncluded(),
            Lazy::create(fn () => $credit->type)->defaultIncluded(),
            Lazy::create(fn () => $credit->description)->defaultIncluded(),
            Lazy::create(fn () => $credit->valid_until)->defaultIncluded(),
            Lazy::create(fn () => $credit->is_system_created)->defaultIncluded(),
            Lazy::create(fn () => $credit->created_at)->defaultIncluded(),
            Lazy::create(fn () => $credit->updated_at)->defaultIncluded(),
            Lazy::create(fn () => UserResponseDTO::from($credit->user)),
            Lazy::create(fn () => $credit->schedule_id ?
                ScheduleResponseDTO::from($credit->schedule) : null),
            Lazy::create(fn () => $credit->issuer_id ?
                UserResponseDTO::from($credit->issuer) : null),
            Lazy::create(fn () => CreditTransactionResponseDTO::collection($credit->transactions)),
        );
    }
}
