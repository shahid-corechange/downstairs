<?php

namespace App\DTOs\LaundryOrder;

use App\DTOs\BaseData;
use App\DTOs\LaundryOrderProduct\LaundryOrderProductRequestDTO;
use App\Enums\LaundryOrder\LaundryOrderStatusEnum;
use App\Rules\ValidLaundryOrderUpdate;
use App\Rules\ValidLaundrySchedule;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\Mappers\CamelCaseMapper;
use Spatie\LaravelData\Optional;

#[MapInputName(CamelCaseMapper::class)]
class UpdateOrderRequestDTO extends BaseData
{
    public function __construct(
        public int $user_id,
        public int|null|Optional $laundry_preference_id,
        // pickup
        public int|null|Optional $pickup_schedule_id,
        // delivery
        public int|null|Optional $delivery_schedule_id,
        // products
        #[DataCollectionOf(LaundryOrderProductRequestDTO::class)]
        public Optional|DataCollection|null $products,
        // message
        public bool|null|Optional $send_message,
        public string|null|Optional $message,
    ) {
    }

    public static function rules(): array
    {
        return [
            'user_id' => 'required|numeric|exists:users,id',
            'laundry_preference_id' => [
                'nullable',
                'numeric',
                'exists:laundry_preferences,id',
                new ValidLaundryOrderUpdate([
                    LaundryOrderStatusEnum::Pending(),
                ]),
            ],
            // pickup
            'pickup_schedule_id' => [
                'nullable',
                'numeric',
                new ValidLaundrySchedule(),
                new ValidLaundryOrderUpdate([
                    LaundryOrderStatusEnum::Pending(),
                ]),
            ],
            // delivery
            'delivery_schedule_id' => [
                'nullable',
                'numeric',
                new ValidLaundrySchedule(),
            ],
            // message
            'send_message' => 'nullable|boolean',
            'message' => 'nullable|string',
        ];
    }
}
