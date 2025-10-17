<?php

namespace App\DTOs\UnassignSubscription;

use App\DTOs\BaseData;
use App\DTOs\LaundryPreference\LaundryPreferenceResponseDTO;
use App\DTOs\Property\PropertyResponseDTO;
use App\DTOs\Store\StoreResponseDTO;
use App\DTOs\Team\TeamResponseDTO;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Lazy;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapInputName(SnakeCaseMapper::class)]
class UnassignSubscriptionLaundryDetailResponseDTO extends BaseData
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
}
