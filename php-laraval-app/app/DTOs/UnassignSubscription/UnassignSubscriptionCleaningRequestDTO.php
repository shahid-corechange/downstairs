<?php

namespace App\DTOs\UnassignSubscription;

use App\DTOs\BaseData;
use App\Rules\CreateSubscriptionTime;
use App\Rules\ValidTeam;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\Validation\Rule;
use Spatie\LaravelData\Mappers\CamelCaseMapper;
use Spatie\LaravelData\Optional;

#[MapInputName(CamelCaseMapper::class)]
class UnassignSubscriptionCleaningRequestDTO extends BaseData
{
    public function __construct(
        #[Rule(['required|numeric|exists:properties,id'])]
        public int $property_id,
        #[Rule(['numeric', new ValidTeam()])]
        public int|null|Optional $team_id,
        #[Rule('required|numeric|min:1')]
        public int $quarters,
        #[Rule(['required', 'date_format:H:i:s', new CreateSubscriptionTime()])]
        public string $start_time,
        public string|Optional $end_time,
    ) {
    }
}
