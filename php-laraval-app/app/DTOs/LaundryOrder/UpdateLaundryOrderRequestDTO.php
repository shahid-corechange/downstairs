<?php

namespace App\DTOs\LaundryOrder;

use App\DTOs\BaseData;
use App\DTOs\LaundryOrderProduct\LaundryOrderProductRequestDTO;
use App\Enums\LaundryOrder\LaundryOrderStatusEnum;
use App\Models\LaundryPreference;
use App\Rules\CollideTeam;
use App\Rules\TimeAfterNow;
use App\Rules\ValidLaundryOrderUpdate;
use App\Rules\ValidStart;
use App\Rules\ValidTeam;
use Carbon\Carbon;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\Mappers\CamelCaseMapper;
use Spatie\LaravelData\Optional;

#[MapInputName(CamelCaseMapper::class)]
class UpdateLaundryOrderRequestDTO extends BaseData
{
    public function __construct(
        public int $laundry_preference_id,
        public string|Optional $ordered_at,
        // pickup
        public int|null|Optional $pickup_property_id,
        public string|null|Optional $pickup_time,
        public int|null|Optional $pickup_team_id,
        // delivery
        public int|null|Optional $delivery_property_id,
        public string|null|Optional $delivery_time,
        public int|null|Optional $delivery_team_id,
        // products
        #[DataCollectionOf(LaundryOrderProductRequestDTO::class)]
        public Optional|DataCollection|null $products,
        // message
        public bool $send_message,
        public string|null|Optional $message,
    ) {
    }

    public static function rules(): array
    {
        return [
            'laundry_preference_id' => [
                'required',
                'numeric',
                'exists:laundry_preferences,id',
                new ValidLaundryOrderUpdate([
                    LaundryOrderStatusEnum::Pending(),
                    LaundryOrderStatusEnum::InProgressPickup(),
                    LaundryOrderStatusEnum::PickedUp(),
                    LaundryOrderStatusEnum::InProgressStore(),
                    LaundryOrderStatusEnum::InProgressLaundry(),
                ]),
            ],
            'ordered_at' => [
                'date_format:Y-m-d',
                'after_or_equal:today',
                new ValidStart(),
                new ValidLaundryOrderUpdate([
                    LaundryOrderStatusEnum::Pending(),
                ]),
            ],
            // pickup
            'pickup_property_id' => [
                'nullable',
                'numeric',
                'exists:properties,id',
                new ValidLaundryOrderUpdate([
                    LaundryOrderStatusEnum::Pending(),
                ]),
            ],
            'pickup_time' => [
                'required_with:pickup_property_id',
                new TimeAfterNow(request('orderedAt')),
                new ValidLaundryOrderUpdate([
                    LaundryOrderStatusEnum::Pending(),
                ]),
            ],
            'pickup_team_id' => [
                'nullable',
                'numeric',
                self::validatePickupTeam(),
                self::collidePickupTeam(),
                new ValidLaundryOrderUpdate([
                    LaundryOrderStatusEnum::Pending(),
                ]),
            ],
            // delivery
            'delivery_property_id' => 'nullable|numeric|exists:properties,id',
            'delivery_time' => [
                'required_with:delivery_property_id',
                new TimeAfterNow(request('orderedAt')),
            ],
            'delivery_team_id' => [
                'nullable',
                'numeric',
                self::validateDeliveryTeam(),
                self::collideDeliveryTeam(),
            ],
            // message
            'send_message' => 'required|boolean',
            'message' => 'nullable|string',
        ];
    }

    private static function validatePickupTeam()
    {
        $teamId = request('pickupTeamId');

        if ($teamId) {
            return new ValidTeam();
        }
    }

    private static function validateDeliveryTeam()
    {
        $teamId = request('deliveryTeamId');

        if ($teamId) {
            return new ValidTeam();
        }
    }

    private static function collidePickupTeam()
    {
        $orderedAt = request('orderedAt');
        $pickupTime = request('pickupTime');

        // Check if the pickup time and ordered is exists
        if (! $orderedAt || ! $pickupTime) {
            return null;
        }

        /** @var \App\Models\LaundryOrder $laundryOrder */
        $laundryOrder = request('laundryOrder');

        $pickupSchedule = $laundryOrder->pickupSchedules->first();
        $excludeScheduleIds = $pickupSchedule ? [$pickupSchedule->id] : [];

        return new CollideTeam(
            Carbon::parse($orderedAt)->setTimeFromTimeString($pickupTime)->toDateTimeString(),
            $excludeScheduleIds,
        );
    }

    private static function collideDeliveryTeam()
    {
        $orderedAt = request('orderedAt');
        $deliveryTime = request('deliveryTime');
        $laundryPreference = LaundryPreference::find(request('laundryPreferenceId'));

        // Check if the delivery time, laundry preference and ordered is exists
        if (! $orderedAt || ! $deliveryTime || ! $laundryPreference) {
            return null;
        }

        /** @var \App\Models\LaundryOrder $laundryOrder */
        $laundryOrder = request('laundryOrder');

        $deliverySchedule = $laundryOrder->deliverySchedules->first();
        $excludeScheduleIds = $deliverySchedule ? [$deliverySchedule->id] : [];

        return new CollideTeam(
            Carbon::parse($orderedAt)
                ->addHours($laundryPreference->hours)
                ->setTimeFromTimeString($deliveryTime)
                ->toDateTimeString(),
            $excludeScheduleIds,
        );
    }
}
