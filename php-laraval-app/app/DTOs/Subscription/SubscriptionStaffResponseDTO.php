<?php

namespace App\DTOs\Subscription;

use App\DTOs\BaseData;
use App\DTOs\User\UserResponseDTO;
use App\Models\SubscriptionStaffDetails;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Lazy;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapInputName(SnakeCaseMapper::class)]
class SubscriptionStaffResponseDTO extends BaseData
{
    public function __construct(
        public Lazy|null|int $id,
        public Lazy|null|int $subscriptionId,
        public Lazy|null|int $userId,
        public Lazy|null|int $quarters,
        public Lazy|null|bool $isActive,
        public Lazy|null|string $description,
        public Lazy|null|string $createdAt,
        public Lazy|null|string $updatedAt,
        public Lazy|null|string $deletedAt,
        public Lazy|null|UserResponseDTO $user,
        public Lazy|null|SubscriptionResponseDTO $subscription,
    ) {
    }

    public static function fromModel(SubscriptionStaffDetails $staffDetails): self
    {
        return new self(
            Lazy::create(fn () => $staffDetails->id)->defaultIncluded(),
            Lazy::create(fn () => $staffDetails->subscription_id)->defaultIncluded(),
            Lazy::create(fn () => $staffDetails->user_id)->defaultIncluded(),
            Lazy::create(fn () => $staffDetails->quarters)->defaultIncluded(),
            Lazy::create(fn () => $staffDetails->is_active)->defaultIncluded(),
            Lazy::create(fn () => $staffDetails->description)->defaultIncluded(),
            Lazy::create(fn () => $staffDetails->created_at)->defaultIncluded(),
            Lazy::create(fn () => $staffDetails->updated_at)->defaultIncluded(),
            Lazy::create(fn () => $staffDetails->deleted_at)->defaultIncluded(),
            Lazy::create(fn () => UserResponseDTO::from($staffDetails->user)),
            Lazy::create(fn () => SubscriptionResponseDTO::from($staffDetails->subscription)),
        );
    }
}
