<?php

namespace App\DTOs\Order;

use App\DTOs\BaseData;
use App\DTOs\Customer\CustomerResponseDTO;
use App\DTOs\FixedPrice\OrderFixedPriceResponseDTO;
use App\DTOs\OrderRow\OrderRowResponseDTO;
use App\DTOs\Schedule\ScheduleResponseDTO;
use App\DTOs\Service\ServiceResponseDTO;
use App\DTOs\Subscription\SubscriptionResponseDTO;
use App\DTOs\User\UserResponseDTO;
use App\Models\Order;
use App\Models\Schedule;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\Lazy;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapInputName(SnakeCaseMapper::class)]
class OrderResponseDTO extends BaseData
{
    public function __construct(
        public Lazy|null|int $id,
        public Lazy|null|int $userId,
        public Lazy|null|int $customerId,
        public Lazy|null|int $serviceId,
        public Lazy|null|int $subscriptionId,
        public Lazy|null|int $orderableId,
        public Lazy|null|string $orderableType,
        public Lazy|null|string $status,
        public Lazy|null|string $paidBy,
        public Lazy|null|string $paidAt,
        public Lazy|null|string $orderedAt,
        public Lazy|null|string $createdAt,
        public Lazy|null|string $updatedAt,
        public Lazy|null|UserResponseDTO $user,
        public Lazy|null|CustomerResponseDTO $customer,
        public Lazy|null|ServiceResponseDTO $service,
        public Lazy|null|SubscriptionResponseDTO $subscription,
        #[DataCollectionOf(OrderRowResponseDTO::class)]
        public Lazy|null|DataCollection $rows,
        public Lazy|null|BaseData $schedule,
        public Lazy|null|BaseData $fixedPrice,
    ) {
    }

    public static function fromModel(Order $order): self
    {

        return new self(
            Lazy::create(fn () => $order->id)->defaultIncluded(),
            Lazy::create(fn () => $order->user_id)->defaultIncluded(),
            Lazy::create(fn () => $order->customer_id)->defaultIncluded(),
            Lazy::create(fn () => $order->service_id)->defaultIncluded(),
            Lazy::create(fn () => $order->subscription_id)->defaultIncluded(),
            Lazy::create(fn () => $order->orderable_id)->defaultIncluded(),
            Lazy::create(fn () => $order->orderable_type)->defaultIncluded(),
            Lazy::create(fn () => $order->status)->defaultIncluded(),
            Lazy::create(fn () => $order->paid_by)->defaultIncluded(),
            Lazy::create(fn () => $order->paid_at)->defaultIncluded(),
            Lazy::create(fn () => $order->ordered_at)->defaultIncluded(),
            Lazy::create(fn () => $order->created_at)->defaultIncluded(),
            Lazy::create(fn () => $order->updated_at)->defaultIncluded(),
            Lazy::create(fn () => UserResponseDTO::from($order->user)),
            Lazy::create(fn () => CustomerResponseDTO::from($order->customer)),
            Lazy::create(fn () => $order->service ?
                ServiceResponseDTO::from($order->service) :
                null),
            Lazy::create(fn () => $order->subscription ?
                SubscriptionResponseDTO::from($order->subscription) :
                null),
            Lazy::create(fn () => OrderRowResponseDTO::collection($order->rows)),
            Lazy::create(fn () => $order->orderable_type === Schedule::class ?
                ScheduleResponseDTO::from($order->orderable) :
                null),
            Lazy::create(fn () => $order->fixedPrice ?
                OrderFixedPriceResponseDTO::from($order->fixedPrice) :
                null),
        );
    }
}
