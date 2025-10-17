<?php

namespace App\DTOs\UnassignSubscription;

use App\DTOs\Addon\AddonResponseDTO;
use App\DTOs\BaseData;
use App\DTOs\Customer\CustomerResponseDTO;
use App\DTOs\CustomTask\CustomTaskResponseDTO;
use App\DTOs\Service\ServiceResponseDTO;
use App\DTOs\User\UserResponseDTO;
use App\Models\UnassignSubscription;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\Lazy;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapInputName(SnakeCaseMapper::class)]
class UnassignSubscriptionResponseDTO extends BaseData
{
    public function __construct(
        public Lazy|null|int $id,
        public Lazy|null|int $userId,
        public Lazy|null|int $customerId,
        public Lazy|null|int $serviceId,
        public Lazy|null|int $frequency,
        public Lazy|null|int $weekday,
        public Lazy|null|int $quarters,
        public Lazy|null|string $startAt,
        public Lazy|null|string $endAt,
        public Lazy|null|string $startTime,
        public Lazy|null|string $endTime,
        public Lazy|null|bool $isFixed,
        public Lazy|null|string $description,
        public Lazy|null|float $totalPrice,
        public Lazy|null|float $totalRawPrice,
        public Lazy|null|float $fixedPrice,
        public Lazy|null|array $productIds,
        public Lazy|null|string $propertyAddress,
        public Lazy|null|string $createdAt,
        public Lazy|null|string $updatedAt,
        public Lazy|null|UserResponseDTO $user,
        public Lazy|null|CustomerResponseDTO $customer,
        public Lazy|null|ServiceResponseDTO $service,
        #[DataCollectionOf(CustomTaskResponseDTO::class)]
        public Lazy|null|DataCollection $tasks,
        #[DataCollectionOf(UnassignSubscriptionProductResponseDTO::class)]
        public Lazy|null|DataCollection $products,
        #[DataCollectionOf(AddonResponseDTO::class)]
        public Lazy|null|DataCollection $addons,
        public Lazy|null|UnassignSubscriptionCleaningDetailResponseDTO $cleaningDetail,
        public Lazy|null|UnassignSubscriptionLaundryDetailResponseDTO $laundryDetail,
    ) {
    }

    public static function fromModel(UnassignSubscription $subscription): self
    {
        return new self(
            Lazy::create(fn () => $subscription->id)->defaultIncluded(),
            Lazy::create(fn () => $subscription->user_id)->defaultIncluded(),
            Lazy::create(fn () => $subscription->customer_id)->defaultIncluded(),
            Lazy::create(fn () => $subscription->service_id)->defaultIncluded(),
            Lazy::create(fn () => $subscription->frequency)->defaultIncluded(),
            Lazy::create(fn () => $subscription->weekday)->defaultIncluded(),
            Lazy::create(fn () => $subscription->quarters)->defaultIncluded(),
            Lazy::create(fn () => $subscription->start_at
                ->setTimeFromTimeString($subscription->start_time))->defaultIncluded(),
            Lazy::create(fn () => $subscription->end_at
                ?->setTimeFromTimeString($subscription->end_time))->defaultIncluded(),
            Lazy::create(fn () => $subscription->start_at
                ->setTimeFromTimeString($subscription->start_time))->defaultIncluded(),
            Lazy::create(fn () => $subscription->start_at
                ->setTimeFromTimeString($subscription->end_time))->defaultIncluded(),
            Lazy::create(fn () => $subscription->is_fixed)->defaultIncluded(),
            Lazy::create(fn () => $subscription->description)->defaultIncluded(),
            Lazy::create(fn () => $subscription->total_price)->defaultIncluded(),
            Lazy::create(fn () => $subscription->total_raw_price)->defaultIncluded(),
            Lazy::create(fn () => $subscription->fixed_price)->defaultIncluded(),
            Lazy::create(fn () => $subscription->product_ids)->defaultIncluded(),
            Lazy::create(fn () => $subscription->property_address)->defaultIncluded(),
            Lazy::create(fn () => $subscription->created_at)->defaultIncluded(),
            Lazy::create(fn () => $subscription->updated_at)->defaultIncluded(),
            Lazy::create(fn () => UserResponseDTO::from($subscription->user)),
            Lazy::create(fn () => CustomerResponseDTO::from($subscription->customer)),
            Lazy::create(fn () => ServiceResponseDTO::from($subscription->service)),
            Lazy::create(fn () => CustomTaskResponseDTO::collection($subscription->tasks)),
            Lazy::create(fn () => UnassignSubscriptionProductResponseDTO::collection($subscription->products)),
            Lazy::create(fn () => AddonResponseDTO::collection($subscription->addons)),
            Lazy::create(
                fn () => UnassignSubscriptionCleaningDetailResponseDTO::from($subscription->cleaningDetail)
            ),
            Lazy::create(
                fn () => UnassignSubscriptionLaundryDetailResponseDTO::from($subscription->laundryDetail)
            ),
        );
    }
}
