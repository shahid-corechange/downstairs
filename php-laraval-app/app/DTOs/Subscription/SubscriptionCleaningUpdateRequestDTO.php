<?php

namespace App\DTOs\Subscription;

use App\DTOs\BaseData;
use App\Rules\UpdateSubscriptionTime;
use App\Rules\ValidTeam;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\Validation\Rule;
use Spatie\LaravelData\Mappers\CamelCaseMapper;
use Spatie\LaravelData\Optional;

#[MapInputName(CamelCaseMapper::class)]
class SubscriptionCleaningUpdateRequestDTO extends BaseData
{
    public function __construct(
        #[Rule(['numeric', new ValidTeam()])]
        public int|Optional $team_id,
        #[Rule('numeric|min:1')]
        public int|Optional $quarters,
        #[Rule(['date_format:H:i:s', new UpdateSubscriptionTime('start_time')])]
        public string|Optional $start_time,
        public string|Optional $end_time, // for use in controller
    ) {
    }
}
