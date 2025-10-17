<?php

namespace App\DTOs\LaundryOrder;

use App\DTOs\BaseData;
use App\Enums\PaymentMethodEnum;
use Illuminate\Validation\Rule;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Mappers\CamelCaseMapper;

#[MapInputName(CamelCaseMapper::class)]
class LaundryOrderPayRequestDTO extends BaseData
{
    public function __construct(
        public string $payment_method,
        public ?string $meta,
    ) {
    }

    public static function rules(): array
    {
        return [
            'payment_method' => [
                'required',
                'string',
                Rule::in(PaymentMethodEnum::values()),
            ],
            'meta' => ['nullable', 'string'],
        ];
    }
}
