<?php

namespace App\DTOs\LaundryOrder;

use App\DTOs\BaseData;
use App\DTOs\LaundryOrderProduct\LaundryOrderProductRequestDTO;
use App\Models\LaundryPreference;
use App\Rules\CollideTeam;
use App\Rules\LaundryDeliveryTime;
use App\Rules\TimeAfterNow;
use App\Rules\ValidStart;
use App\Rules\ValidTeam;
use Carbon\Carbon;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\Mappers\CamelCaseMapper;

#[MapInputName(CamelCaseMapper::class)]
class CreateLaundryOrderRequestDTO extends BaseData
{
    public function __construct(
        public int $laundry_preference_id,
        public int $user_id,
        public string $ordered_at,
        // pickup
        public ?int $pickup_property_id,
        public ?string $pickup_time,
        public ?int $pickup_team_id,
        // delivery
        public ?int $delivery_property_id,
        public ?string $delivery_time,
        public ?int $delivery_team_id,
        // products
        #[DataCollectionOf(LaundryOrderProductRequestDTO::class)]
        public DataCollection $products,
        // message
        public bool $send_message,
        public ?string $message,
    ) {
    }

    public static function rules(): array
    {
        return [
            'laundry_preference_id' => 'required|numeric|exists:laundry_preferences,id',
            'user_id' => 'required|numeric|exists:users,id',
            'ordered_at' => [
                'required',
                'date_format:Y-m-d',
                'after_or_equal:today',
                new ValidStart(),
            ],
            // pickup
            'pickup_property_id' => 'nullable|numeric|exists:properties,id',
            'pickup_time' => ['required_with:pickup_property_id', new TimeAfterNow()],
            'pickup_team_id' => [
                'nullable',
                'numeric',
                self::validatePickupTeam(),
                self::collidePickupTeam(),
            ],
            // delivery
            'delivery_property_id' => 'nullable|numeric|exists:properties,id',
            'delivery_time' => ['required_with:delivery_property_id', new TimeAfterNow(), new LaundryDeliveryTime()],
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
        $teamId = request('pickupTeamId');

        if ($teamId) {
            $orderedAt = request('orderedAt');
            $pickupTime = request('pickupTime');

            if ($orderedAt && $pickupTime) {
                return new CollideTeam(
                    Carbon::parse($orderedAt)->setTimeFromTimeString($pickupTime)->toDateTimeString()
                );
            }
        }
    }

    private static function collideDeliveryTeam()
    {
        $teamId = request('deliveryTeamId');

        if ($teamId) {
            $orderedAt = request('orderedAt');
            $deliveryTime = request('deliveryTime');
            $laundryPreference = LaundryPreference::find(request('laundryPreferenceId'));

            if ($orderedAt && $deliveryTime) {
                return new CollideTeam(
                    Carbon::parse($orderedAt)
                        ->addHours($laundryPreference->hours)
                        ->setTimeFromTimeString($deliveryTime)
                        ->toDateTimeString()
                );
            }
        }
    }
}
