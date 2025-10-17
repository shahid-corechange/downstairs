<?php

namespace App\DTOs\Subscription;

use App\DTOs\Addon\AddonResponseDTO;
use App\DTOs\BaseData;
use App\DTOs\Customer\CustomerResponseDTO;
use App\DTOs\CustomTask\CustomTaskResponseDTO;
use App\DTOs\FixedPrice\FixedPriceResponseDTO;
use App\DTOs\Product\ProductResponseDTO;
use App\DTOs\Schedule\ScheduleResponseDTO;
use App\DTOs\Service\ServiceResponseDTO;
use App\DTOs\User\UserResponseDTO;
use App\Models\Subscription;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\Lazy;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapInputName(SnakeCaseMapper::class)]
class SubscriptionResponseDTO extends BaseData
{
    public function __construct(
        public Lazy|null|int $id,
        public Lazy|null|int $userId,
        public Lazy|null|int $customerId,
        public Lazy|null|int $serviceId,
        public Lazy|null|int $fixedPriceId,
        public Lazy|null|int $frequency,
        public Lazy|null|int $weekday,
        public Lazy|null|string $startAt,
        public Lazy|null|string $endAt,
        public Lazy|null|string $startTime,
        public Lazy|null|string $endTime,
        public Lazy|null|bool $isPaused,
        public Lazy|null|bool $isFixed,
        public Lazy|null|string $description,
        public Lazy|null|float $totalPrice,
        public Lazy|null|float $totalRawPrice,
        public Lazy|null|string $createdAt,
        public Lazy|null|string $updatedAt,
        public Lazy|null|string $deletedAt,
        public Lazy|null|string $subscribableType,
        public Lazy|null|int $subscribableId,
        public Lazy|null|bool $isCleaningHasLaundry,
        public Lazy|null|UserResponseDTO $user,
        public Lazy|null|CustomerResponseDTO $customer,
        public Lazy|null|ServiceResponseDTO $service,
        #[DataCollectionOf(CustomTaskResponseDTO::class)]
        public Lazy|null|DataCollection $tasks,
        #[DataCollectionOf(ProductResponseDTO::class)]
        public Lazy|null|DataCollection $products,
        #[DataCollectionOf(AddonResponseDTO::class)]
        public Lazy|null|DataCollection $addons,
        public Lazy|null|FixedPriceResponseDTO $fixedPrice,
        #[DataCollectionOf(ScheduleResponseDTO::class)]
        public Lazy|null|DataCollection $updatedSchedules,
        public Lazy|null|BaseData $detail,
    ) {
    }

    public static function fromModel(Subscription $subscription): self
    {
        return new self(
            Lazy::create(fn () => $subscription->id)->defaultIncluded(),
            Lazy::create(fn () => $subscription->user_id)->defaultIncluded(),
            Lazy::create(fn () => $subscription->customer_id)->defaultIncluded(),
            Lazy::create(fn () => $subscription->service_id)->defaultIncluded(),
            Lazy::create(fn () => $subscription->fixed_price_id)->defaultIncluded(),
            Lazy::create(fn () => $subscription->frequency)->defaultIncluded(),
            Lazy::create(fn () => $subscription->weekday)->defaultIncluded(),
            Lazy::create(fn () => $subscription->start_at
                ->setTimeFromTimeString($subscription->start_time))->defaultIncluded(),
            Lazy::create(fn () => $subscription->end_at
                ?->setTimeFromTimeString($subscription->end_time))->defaultIncluded(),
            Lazy::create(fn () => $subscription->start_at
                ->setTimeFromTimeString($subscription->start_time))->defaultIncluded(),
            Lazy::create(fn () => $subscription->start_at
                ->setTimeFromTimeString($subscription->end_time))->defaultIncluded(),
            Lazy::create(fn () => $subscription->is_paused)->defaultIncluded(),
            Lazy::create(fn () => $subscription->is_fixed)->defaultIncluded(),
            Lazy::create(fn () => $subscription->description)->defaultIncluded(),
            Lazy::create(fn () => $subscription->total_price)->defaultIncluded(),
            Lazy::create(fn () => $subscription->total_raw_price)->defaultIncluded(),
            Lazy::create(fn () => $subscription->created_at)->defaultIncluded(),
            Lazy::create(fn () => $subscription->updated_at)->defaultIncluded(),
            Lazy::create(fn () => $subscription->deleted_at)->defaultIncluded(),
            Lazy::create(fn () => $subscription->subscribable_type)->defaultIncluded(),
            Lazy::create(fn () => $subscription->subscribable_id)->defaultIncluded(),
            Lazy::create(fn () => $subscription->is_cleaning_has_laundry)->defaultIncluded(),
            Lazy::create(fn () => UserResponseDTO::from($subscription->user)),
            Lazy::create(fn () => CustomerResponseDTO::from($subscription->customer)),
            Lazy::create(fn () => ServiceResponseDTO::from($subscription->service)),
            Lazy::create(fn () => CustomTaskResponseDTO::collection($subscription->tasks)),
            Lazy::create(fn () => ProductResponseDTO::collection($subscription->products)),
            Lazy::create(fn () => AddonResponseDTO::collection($subscription->addons)),
            Lazy::create(fn () => FixedPriceResponseDTO::from($subscription->fixedPrice)),
            Lazy::create(fn () => ScheduleResponseDTO::collection($subscription->updatedSchedules)),
            Lazy::create(fn () => $subscription->isCleaning() ?
                SubscriptionCleaningDetailResponseDTO::from($subscription->subscribable) :
                SubscriptionLaundryDetailResponseDTO::from($subscription->subscribable)),
        );
    }
}
