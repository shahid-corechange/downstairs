<?php

namespace App\DTOs\Notification;

use App\DTOs\BaseData;
use App\DTOs\User\UserResponseDTO;
use App\Models\Notification;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Lazy;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapInputName(SnakeCaseMapper::class)]
class NotificationResponseDTO extends BaseData
{
    public function __construct(
        public Lazy|null|int $id,
        public Lazy|null|int $userId,
        public Lazy|null|string $type,
        public Lazy|null|string $title,
        public Lazy|null|string $description,
        public Lazy|null|bool $isRead,
        public Lazy|null|string $createdAt,
        public Lazy|null|string $updatedAt,
        public Lazy|null|UserResponseDTO $user,
    ) {
    }

    public static function fromModel(Notification $notification): self
    {
        return new self(
            Lazy::create(fn () => $notification->id)->defaultIncluded(),
            Lazy::create(fn () => $notification->user_id)->defaultIncluded(),
            Lazy::create(fn () => $notification->type)->defaultIncluded(),
            Lazy::create(fn () => $notification->title)->defaultIncluded(),
            Lazy::create(fn () => $notification->description)->defaultIncluded(),
            Lazy::create(fn () => $notification->is_read)->defaultIncluded(),
            Lazy::create(fn () => $notification->created_at)->defaultIncluded(),
            Lazy::create(fn () => $notification->updated_at)->defaultIncluded(),
            Lazy::create(fn () => UserResponseDTO::from($notification->user)),
        );
    }
}
