<?php

namespace App\DTOs\LaundryOrder;

use App\DTOs\BaseData;
use App\DTOs\LaundryOrderProduct\LaundryOrderProductRequestDTO;
use App\Rules\ValidLaundrySchedule;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\Mappers\CamelCaseMapper;

#[MapInputName(CamelCaseMapper::class)]
class CreateOrderRequestDTO extends BaseData
{
    public function __construct(
        public int $laundry_preference_id,
        public int $user_id,
        // pickup
        public ?int $pickup_schedule_id,
        // delivery
        public ?int $delivery_schedule_id,
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
            // pickup
            'pickup_schedule_id' => [
                'nullable',
                'numeric',
                new ValidLaundrySchedule(),
            ],
            // delivery
            'delivery_schedule_id' => [
                'nullable',
                'numeric',
                new ValidLaundrySchedule(),
            ],
            // message
            'send_message' => 'required|boolean',
            'message' => 'nullable|string',
            'products' => 'required',
        ];
    }
}
