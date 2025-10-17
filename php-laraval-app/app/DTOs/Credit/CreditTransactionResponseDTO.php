<?php

namespace App\DTOs\Credit;

use App\DTOs\BaseData;
use App\DTOs\Schedule\ScheduleResponseDTO;
use App\DTOs\User\UserResponseDTO;
use App\Models\CreditTransaction;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\Lazy;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapInputName(SnakeCaseMapper::class)]
class CreditTransactionResponseDTO extends BaseData
{
    public function __construct(
        public Lazy|null|int $id,
        public Lazy|null|int $scheduleId,
        public Lazy|null|int $issuerId,
        public Lazy|null|string $type,
        public Lazy|null|int $totalAmount,
        public Lazy|null|string $description,
        public Lazy|null|string $createdAt,
        public Lazy|null|string $updatedAt,
        public Lazy|null|ScheduleResponseDTO $schedule,
        public Lazy|null|UserResponseDTO $issuer,
        #[DataCollectionOf(CreditResponseDTO::class)]
        public Lazy|null|DataCollection $credits,
    ) {
    }

    public static function fromModel(CreditTransaction $creditTransaction): self
    {
        return new self(
            Lazy::create(fn () => $creditTransaction->id)->defaultIncluded(),
            Lazy::create(fn () => $creditTransaction->schedule_id)->defaultIncluded(),
            Lazy::create(fn () => $creditTransaction->issuer_id)->defaultIncluded(),
            Lazy::create(fn () => $creditTransaction->type)->defaultIncluded(),
            Lazy::create(fn () => $creditTransaction->total_amount)->defaultIncluded(),
            Lazy::create(fn () => $creditTransaction->description)->defaultIncluded(),
            Lazy::create(fn () => $creditTransaction->created_at)->defaultIncluded(),
            Lazy::create(fn () => $creditTransaction->updated_at)->defaultIncluded(),
            Lazy::create(fn () => ScheduleResponseDTO::from(
                $creditTransaction->schedule
            )),
            Lazy::create(fn () => UserResponseDTO::from(
                $creditTransaction->issuer
            )),
            Lazy::create(fn () => CreditResponseDTO::collection($creditTransaction->credits)),
        );
    }
}
