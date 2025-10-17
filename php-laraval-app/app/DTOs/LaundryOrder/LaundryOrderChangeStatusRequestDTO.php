<?php

namespace App\DTOs\LaundryOrder;

use App\DTOs\BaseData;
use App\Enums\LaundryOrder\LaundryOrderStatusEnum;
use Illuminate\Validation\Rule;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Mappers\CamelCaseMapper;
use Spatie\LaravelData\Optional;

#[MapInputName(CamelCaseMapper::class)]
class LaundryOrderChangeStatusRequestDTO extends BaseData
{
    public function __construct(
        public string $status,
        // message
        public bool|null|Optional $send_message,
        public string|null|Optional $message,
    ) {
    }

    public static function rules(): array
    {
        return [
            'status' => [
                'required',
                'string',
                Rule::in([
                    LaundryOrderStatusEnum::InProgressPickup(),
                    LaundryOrderStatusEnum::PickedUp(),
                    LaundryOrderStatusEnum::InProgressStore(),
                    LaundryOrderStatusEnum::InProgressLaundry(),
                    LaundryOrderStatusEnum::InProgressDelivery(),
                    LaundryOrderStatusEnum::Delivered(),
                    LaundryOrderStatusEnum::Paid(),
                    LaundryOrderStatusEnum::Done(),
                    LaundryOrderStatusEnum::Closed(),
                ]),
            ],
            // message
            'send_message' => 'nullable|boolean',
            'message' => 'nullable|string',
        ];
    }
}
