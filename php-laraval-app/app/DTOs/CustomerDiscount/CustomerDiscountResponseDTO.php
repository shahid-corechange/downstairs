<?php

namespace App\DTOs\CustomerDiscount;

use App\DTOs\BaseData;
use App\DTOs\User\UserResponseDTO;
use App\Models\CustomerDiscount;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Lazy;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapInputName(SnakeCaseMapper::class)]
class CustomerDiscountResponseDTO extends BaseData
{
    public function __construct(
        public Lazy|null|int $id,
        public Lazy|null|int $userId,
        public Lazy|null|string $type,
        public Lazy|null|int $value,
        public Lazy|null|string $startDate,
        public Lazy|null|string $endDate,
        public Lazy|null|int $usageLimit,
        public Lazy|null|bool $isActive,
        public Lazy|null|string $createdAt,
        public Lazy|null|string $updatedAt,
        public Lazy|null|string $deletedAt,
        public Lazy|null|UserResponseDTO $user,
    ) {
    }

    public static function fromModel(CustomerDiscount $customerDiscount): self
    {
        return new self(
            Lazy::create(fn () => $customerDiscount->id)->defaultIncluded(),
            Lazy::create(fn () => $customerDiscount->user_id)->defaultIncluded(),
            Lazy::create(fn () => $customerDiscount->type)->defaultIncluded(),
            Lazy::create(fn () => $customerDiscount->value)->defaultIncluded(),
            Lazy::create(fn () => $customerDiscount->start_date)->defaultIncluded(),
            Lazy::create(fn () => $customerDiscount->end_date)->defaultIncluded(),
            Lazy::create(fn () => $customerDiscount->usage_limit)->defaultIncluded(),
            Lazy::create(fn () => $customerDiscount->is_active)->defaultIncluded(),
            Lazy::create(fn () => $customerDiscount->created_at)->defaultIncluded(),
            Lazy::create(fn () => $customerDiscount->updated_at)->defaultIncluded(),
            Lazy::create(fn () => $customerDiscount->deleted_at)->defaultIncluded(),
            Lazy::create(fn () => UserResponseDTO::from($customerDiscount->user)),
        );
    }
}
