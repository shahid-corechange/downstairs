<?php

namespace App\DTOs\Subscription;

use App\DTOs\BaseData;
use App\DTOs\Property\PropertyResponseDTO;
use App\DTOs\Team\TeamResponseDTO;
use App\Models\SubscriptionCleaningDetail;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Lazy;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapInputName(SnakeCaseMapper::class)]
class SubscriptionCleaningDetailResponseDTO extends BaseData
{
    public function __construct(
        public Lazy|null|int $id,
        public Lazy|null|int $propertyId,
        public Lazy|null|int $teamId,
        public Lazy|null|int $quarters,
        public Lazy|null|string $startTime,
        public Lazy|null|string $endTime,
        public Lazy|null|string $teamName,
        public Lazy|null|string $address,
        public Lazy|null|PropertyResponseDTO $property,
        public Lazy|null|TeamResponseDTO $team,
    ) {
    }

    public static function fromModel(SubscriptionCleaningDetail $detail): self
    {
        return new self(
            Lazy::create(fn () => $detail->id)->defaultIncluded(),
            Lazy::create(fn () => $detail->property_id)->defaultIncluded(),
            Lazy::create(fn () => $detail->team_id)->defaultIncluded(),
            Lazy::create(fn () => $detail->quarters)->defaultIncluded(),
            Lazy::create(fn () => $detail->start_time)->defaultIncluded(),
            Lazy::create(fn () => $detail->end_time)->defaultIncluded(),
            Lazy::create(fn () => $detail->team_name)->defaultIncluded(),
            Lazy::create(fn () => $detail->address)->defaultIncluded(),
            Lazy::create(fn () => PropertyResponseDTO::from($detail->property)),
            Lazy::create(fn () => TeamResponseDTO::from($detail->team)),
        );
    }
}
