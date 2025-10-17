<?php

namespace App\DTOs\Feedback;

use App\DTOs\BaseData;
use App\DTOs\User\UserResponseDTO;
use App\Models\Feedback;
use App\Models\User;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Lazy;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapInputName(SnakeCaseMapper::class)]
class FeedbackResponseDTO extends BaseData
{
    public function __construct(
        public Lazy|null|int $id,
        public Lazy|null|int $userId,
        public Lazy|null|string $option,
        public Lazy|null|string $description,
        public Lazy|null|string $createdAt,
        public Lazy|null|string $updatedAt,
        public Lazy|null|string $deletedAt,
        public Lazy|null|BaseData $user,
    ) {
    }

    public static function fromModel(Feedback $feedback): self
    {
        return new self(
            Lazy::create(fn () => $feedback->id)->defaultIncluded(),
            Lazy::create(fn () => $feedback->feedbackable_type === User::class ?
                $feedback->feedbackable_id : null),
            Lazy::create(fn () => $feedback->option)->defaultIncluded(),
            Lazy::create(fn () => $feedback->description)->defaultIncluded(),
            Lazy::create(fn () => $feedback->created_at)->defaultIncluded(),
            Lazy::create(fn () => $feedback->updated_at)->defaultIncluded(),
            Lazy::create(fn () => $feedback->deleted_at)->defaultIncluded(),
            Lazy::create(fn () => $feedback->feedbackable_type === User::class ?
                UserResponseDTO::from($feedback->feedbackable) :
                null),
        );
    }
}
