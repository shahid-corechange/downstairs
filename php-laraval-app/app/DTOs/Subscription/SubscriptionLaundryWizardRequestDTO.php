<?php

namespace App\DTOs\Subscription;

use App\DTOs\BaseData;
use App\Rules\CreateSubscriptionTime;
use App\Rules\ValidTeam;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\Validation\Rule;
use Spatie\LaravelData\Mappers\CamelCaseMapper;
use Spatie\LaravelData\Optional;

#[MapInputName(CamelCaseMapper::class)]
class SubscriptionLaundryWizardRequestDTO extends BaseData
{
    public function __construct(
        // plan
        #[Rule('required|numeric|exists:stores,id')]
        public int $store_id,
        #[Rule('required|numeric|exists:laundry_preferences,id')]
        public int $laundry_preference_id,
        // pickup
        #[Rule(['nullable', 'numeric', 'exists:properties,id'])]
        public int|null|Optional $pickup_property_id,
        #[Rule(['required_with:pickup_property_id', 'numeric', new ValidTeam()])]
        public int|null|Optional $pickup_team_id,
        #[Rule([
            'nullable',
            'date_format:H:i:s',
            'required_with:pickup_property_id',
            new CreateSubscriptionTime(),
        ])]
        public string|null|Optional $pickup_time,
        // delivery
        #[Rule(['nullable', 'numeric', 'exists:properties,id'])]
        public int|null|Optional $delivery_property_id,
        #[Rule(['required_with:delivery_property_id', 'numeric', new ValidTeam()])]
        public int|null|Optional $delivery_team_id,
        #[Rule([
            'nullable',
            'date_format:H:i:s',
            'required_with:delivery_property_id',
            new CreateSubscriptionTime(),
        ])]
        public string|null|Optional $delivery_time,
    ) {
    }
}
