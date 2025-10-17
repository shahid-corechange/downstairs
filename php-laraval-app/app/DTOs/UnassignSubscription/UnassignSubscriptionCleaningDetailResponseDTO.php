<?php

namespace App\DTOs\UnassignSubscription;

use App\DTOs\BaseData;
use App\DTOs\Property\PropertyResponseDTO;
use App\DTOs\Team\TeamResponseDTO;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Lazy;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapInputName(SnakeCaseMapper::class)]
class UnassignSubscriptionCleaningDetailResponseDTO extends BaseData
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
}
