<?php

namespace App\DTOs\Log;

use App\DTOs\BaseData;
use App\DTOs\User\UserResponseDTO;
use App\Models\Activity;
use App\Models\User;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Lazy;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapInputName(SnakeCaseMapper::class)]
class ActivityLogResponseDTO extends BaseData
{
    public function __construct(
        public Lazy|null|int $id,
        public Lazy|null|string $logName,
        public Lazy|null|string $description,
        public Lazy|null|string $subjectType,
        public Lazy|null|string $event,
        public Lazy|null|int $subjectId,
        public Lazy|null|string $causerType,
        public Lazy|null|int $causerId,
        public Lazy|null|array $properties,
        public Lazy|null|string $batchUuid,
        public Lazy|null|string $createdAt,
        public Lazy|null|string $updateAt,
        public Lazy|null|BaseData $user,
    ) {
    }

    public static function fromModel(Activity $log): self
    {
        return new self(
            Lazy::create(fn () => $log->id)->defaultIncluded(),
            Lazy::create(fn () => $log->log_name)->defaultIncluded(),
            Lazy::create(fn () => $log->description)->defaultIncluded(),
            Lazy::create(fn () => $log->subject_type)->defaultIncluded(),
            Lazy::create(fn () => $log->event)->defaultIncluded(),
            Lazy::create(fn () => $log->subject_id)->defaultIncluded(),
            Lazy::create(fn () => $log->causer_type)->defaultIncluded(),
            Lazy::create(fn () => $log->causer_id)->defaultIncluded(),
            Lazy::create(fn () => $log->properties)->defaultIncluded(),
            Lazy::create(fn () => $log->batch_uuid)->defaultIncluded(),
            Lazy::create(fn () => $log->created_at)->defaultIncluded(),
            Lazy::create(fn () => $log->updated_at)->defaultIncluded(),
            Lazy::create(fn () => $log->causer_type === User::class ?
                UserResponseDTO::from($log->causer) :
                null),
        );
    }
}
