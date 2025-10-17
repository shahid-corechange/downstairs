<?php

namespace App\DTOs\Subscription;

use App\DTOs\BaseData;
use App\DTOs\LaundryPreference\LaundryPreferenceResponseDTO;
use App\DTOs\Property\PropertyResponseDTO;
use App\DTOs\Store\StoreResponseDTO;
use App\DTOs\Team\TeamResponseDTO;
use App\Models\SubscriptionLaundryDetail;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Lazy;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapInputName(SnakeCaseMapper::class)]
class SubscriptionLaundryDetailResponseDTO extends BaseData
{
    public function __construct(
        public Lazy|null|int $id,
        public Lazy|null|int $storeId,
        public Lazy|null|int $laundryPreferenceId,
        public Lazy|null|int $pickupPropertyId,
        public Lazy|null|int $pickupTeamId,
        public Lazy|null|string $pickupTime,
        public Lazy|null|int $deliveryPropertyId,
        public Lazy|null|int $deliveryTeamId,
        public Lazy|null|string $deliveryTime,
        public Lazy|null|string $startTime,
        public Lazy|null|string $endTime,
        public Lazy|null|string $teamName,
        public Lazy|null|string $address,
        public Lazy|null|int $quarters,
        public Lazy|null|StoreResponseDTO $store,
        public Lazy|null|LaundryPreferenceResponseDTO $laundryPreference,
        public Lazy|null|PropertyResponseDTO $pickupProperty,
        public Lazy|null|TeamResponseDTO $pickupTeam,
        public Lazy|null|PropertyResponseDTO $deliveryProperty,
        public Lazy|null|TeamResponseDTO $deliveryTeam,
    ) {
    }

    public static function fromModel(SubscriptionLaundryDetail $detail): self
    {
        return new self(
            Lazy::create(fn () => $detail->id)->defaultIncluded(),
            Lazy::create(fn () => $detail->store_id)->defaultIncluded(),
            Lazy::create(fn () => $detail->laundry_preference_id)->defaultIncluded(),
            Lazy::create(fn () => $detail->pickup_property_id)->defaultIncluded(),
            Lazy::create(fn () => $detail->pickup_team_id)->defaultIncluded(),
            Lazy::create(fn () => $detail->pickup_time)->defaultIncluded(),
            Lazy::create(fn () => $detail->delivery_property_id)->defaultIncluded(),
            Lazy::create(fn () => $detail->delivery_team_id)->defaultIncluded(),
            Lazy::create(fn () => $detail->delivery_time)->defaultIncluded(),
            Lazy::create(fn () => $detail->start_time)->defaultIncluded(),
            Lazy::create(fn () => $detail->end_time)->defaultIncluded(),
            Lazy::create(fn () => $detail->team_name)->defaultIncluded(),
            Lazy::create(fn () => $detail->address)->defaultIncluded(),
            Lazy::create(fn () => $detail->quarters)->defaultIncluded(),
            Lazy::create(fn () => StoreResponseDTO::from($detail->store)),
            Lazy::create(fn () => LaundryPreferenceResponseDTO::from($detail->laundry_preference)),
            Lazy::create(fn () => $detail->pickupProperty ? PropertyResponseDTO::from($detail->pickupProperty) : null),
            Lazy::create(fn () => $detail->pickup_team ? TeamResponseDTO::from($detail->pickup_team) : null),
            Lazy::create(fn () => $detail->deliveryProperty ?
                PropertyResponseDTO::from($detail->deliveryProperty) : null),
            Lazy::create(fn () => $detail->delivery_team ? TeamResponseDTO::from($detail->delivery_team) : null),
        );
    }
}
