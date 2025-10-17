<?php

namespace App\DTOs\LaundryOrder;

use App\DTOs\BaseData;
use App\DTOs\Customer\CustomerResponseDTO;
use App\DTOs\LaundryOrderHistory\LaundryOrderHistoryResponseDTO;
use App\DTOs\LaundryOrderProduct\LaundryOrderProductResponseDTO;
use App\DTOs\LaundryPreference\LaundryPreferenceResponseDTO;
use App\DTOs\Property\PropertyResponseDTO;
use App\DTOs\Schedule\ScheduleResponseDTO;
use App\DTOs\Store\StoreResponseDTO;
use App\DTOs\Subscription\SubscriptionResponseDTO;
use App\DTOs\Team\TeamResponseDTO;
use App\DTOs\User\UserResponseDTO;
use App\Models\LaundryOrder;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\Lazy;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapInputName(SnakeCaseMapper::class)]
class LaundryOrderResponseDTO extends BaseData
{
    public function __construct(
        public Lazy|null|int $id,
        public Lazy|null|int $storeId,
        public Lazy|null|int $laundryPreferenceId,
        public Lazy|null|int $subscriptionId,
        public Lazy|null|int $userId,
        public Lazy|null|int $causerId,
        public Lazy|null|int $customerId,
        public Lazy|null|int $pickupPropertyId,
        public Lazy|null|int $pickupTeamId,
        public Lazy|null|string $pickupTime,
        public Lazy|null|int $deliveryPropertyId,
        public Lazy|null|int $deliveryTeamId,
        public Lazy|null|int $pickupInCleaningId,
        public Lazy|null|int $deliveryInCleaningId,
        public Lazy|null|string $deliveryTime,
        public Lazy|null|string $paymentMethod,
        public Lazy|null|string $orderSource,
        public Lazy|null|string $orderedAt,
        public Lazy|null|string $paidAt,
        public Lazy|null|string $dueAt,
        public Lazy|null|float $totalRut,
        public Lazy|null|float $totalPriceWithVat,
        public Lazy|null|float $totalPriceWithDiscount,
        public Lazy|null|float $totalDiscount,
        public Lazy|null|Collection $totalVat,
        public Lazy|null|float $totalToPay,
        public Lazy|null|float $roundAmount,
        public Lazy|null|float $preferenceAmount,
        public Lazy|null|string $status,
        public Lazy|null|string $createdAt,
        public Lazy|null|string $updatedAt,
        public Lazy|null|string $deletedAt,
        public Lazy|null|StoreResponseDTO $store,
        public Lazy|null|UserResponseDTO $user,
        public Lazy|null|UserResponseDTO $causer,
        public Lazy|null|LaundryPreferenceResponseDTO $laundryPreference,
        public Lazy|null|SubscriptionResponseDTO $subscription,
        public Lazy|null|BaseData $customer,
        public Lazy|null|PropertyResponseDTO $pickupProperty,
        public Lazy|null|TeamResponseDTO $pickupTeam,
        public Lazy|null|PropertyResponseDTO $deliveryProperty,
        public Lazy|null|TeamResponseDTO $deliveryTeam,
        #[DataCollectionOf(LaundryOrderProductResponseDTO::class)]
        public Lazy|null|DataCollection $products,
        #[DataCollectionOf(ScheduleResponseDTO::class)]
        public Lazy|null|DataCollection $schedules,
        #[DataCollectionOf(LaundryOrderHistoryResponseDTO::class)]
        public Lazy|null|DataCollection $histories,
        public Lazy|null|ScheduleResponseDTO $pickupInCleaning,
        public Lazy|null|ScheduleResponseDTO $deliveryInCleaning,
    ) {
    }

    public static function fromModel(LaundryOrder $laundryOrder): self
    {

        return new self(
            Lazy::create(fn () => $laundryOrder->id)->defaultIncluded(),
            Lazy::create(fn () => $laundryOrder->store_id)->defaultIncluded(),
            Lazy::create(fn () => $laundryOrder->laundry_preference_id)->defaultIncluded(),
            Lazy::create(fn () => $laundryOrder->subscription_id)->defaultIncluded(),
            Lazy::create(fn () => $laundryOrder->user_id)->defaultIncluded(),
            Lazy::create(fn () => $laundryOrder->causer_id)->defaultIncluded(),
            Lazy::create(fn () => $laundryOrder->customer_id)->defaultIncluded(),
            Lazy::create(fn () => $laundryOrder->pickup_property_id)->defaultIncluded(),
            Lazy::create(fn () => $laundryOrder->pickup_team_id)->defaultIncluded(),
            Lazy::create(fn () => $laundryOrder->pickup_time)->defaultIncluded(),
            Lazy::create(fn () => $laundryOrder->delivery_property_id)->defaultIncluded(),
            Lazy::create(fn () => $laundryOrder->delivery_team_id)->defaultIncluded(),
            Lazy::create(fn () => $laundryOrder->pickup_in_cleaning_id)->defaultIncluded(),
            Lazy::create(fn () => $laundryOrder->delivery_in_cleaning_id)->defaultIncluded(),
            Lazy::create(fn () => $laundryOrder->delivery_time)->defaultIncluded(),
            Lazy::create(fn () => $laundryOrder->payment_method)->defaultIncluded(),
            Lazy::create(fn () => $laundryOrder->order_source)->defaultIncluded(),
            Lazy::create(fn () => $laundryOrder->ordered_at)->defaultIncluded(),
            Lazy::create(fn () => $laundryOrder->paid_at)->defaultIncluded(),
            Lazy::create(fn () => $laundryOrder->due_at)->defaultIncluded(),
            Lazy::create(fn () => $laundryOrder->total_rut)->defaultIncluded(),
            Lazy::create(fn () => $laundryOrder->total_price_with_vat)->defaultIncluded(),
            Lazy::create(fn () => $laundryOrder->total_price_with_discount)->defaultIncluded(),
            Lazy::create(fn () => $laundryOrder->total_discount)->defaultIncluded(),
            Lazy::create(fn () => $laundryOrder->total_vat)->defaultIncluded(),
            Lazy::create(fn () => $laundryOrder->total_to_pay)->defaultIncluded(),
            Lazy::create(fn () => $laundryOrder->round_amount)->defaultIncluded(),
            Lazy::create(fn () => $laundryOrder->preference_amount)->defaultIncluded(),
            Lazy::create(fn () => $laundryOrder->status)->defaultIncluded(),
            Lazy::create(fn () => $laundryOrder->created_at)->defaultIncluded(),
            Lazy::create(fn () => $laundryOrder->updated_at)->defaultIncluded(),
            Lazy::create(fn () => $laundryOrder->deleted_at)->defaultIncluded(),
            Lazy::create(fn () => StoreResponseDTO::from($laundryOrder->store)),
            Lazy::create(fn () => UserResponseDTO::from($laundryOrder->user)),
            Lazy::create(fn () => UserResponseDTO::from($laundryOrder->causer)),
            Lazy::create(fn () => LaundryPreferenceResponseDTO::from($laundryOrder->preference)),
            Lazy::create(fn () => $laundryOrder->subscription ?
                SubscriptionResponseDTO::from($laundryOrder->subscription) : null),
            Lazy::create(fn () => $laundryOrder->customer ?
                CustomerResponseDTO::from($laundryOrder->customer) : null),
            Lazy::create(fn () => $laundryOrder->pickupProperty ?
                PropertyResponseDTO::from($laundryOrder->pickupProperty) : null),
            Lazy::create(fn () => $laundryOrder->pickupTeam ?
                TeamResponseDTO::from($laundryOrder->pickupTeam) : null),
            Lazy::create(fn () => $laundryOrder->deliveryProperty ?
                PropertyResponseDTO::from($laundryOrder->deliveryProperty) : null),
            Lazy::create(fn () => $laundryOrder->deliveryTeam ?
                TeamResponseDTO::from($laundryOrder->deliveryTeam) : null),
            Lazy::create(fn () => LaundryOrderProductResponseDTO::collection($laundryOrder->products)),
            Lazy::create(fn () => ScheduleResponseDTO::collection($laundryOrder->schedules)),
            Lazy::create(fn () => LaundryOrderHistoryResponseDTO::collection($laundryOrder->histories)),
            Lazy::create(fn () => $laundryOrder->pickup_in_cleaning_id ?
                ScheduleResponseDTO::from($laundryOrder->pickupInCleanings()->first()) : null),
            Lazy::create(fn () => $laundryOrder->delivery_in_cleaning_id ?
                ScheduleResponseDTO::from($laundryOrder->deliveryInCleanings()->first()) : null),
        );
    }
}
