<?php

namespace App\DTOs\StoreSale;

use App\DTOs\BaseData;
use App\Enums\PaymentMethodEnum;
use Illuminate\Validation\Rule;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\Lazy;
use Spatie\LaravelData\Mappers\CamelCaseMapper;

#[MapInputName(CamelCaseMapper::class)]
class CreateStoreSaleRequestDTO extends BaseData
{
    public function __construct(
        public string $user_id,
        public string $customer_id,
        public string $payment_method,
        public ?string $meta,
        #[DataCollectionOf(StoreSaleProductRequestDTO::class)]
        public Lazy|null|DataCollection $products,
    ) {
    }

    public static function rules()
    {
        return [
            'user_id' => 'required|string|exists:users,id',
            'customer_id' => 'required|string|exists:customers,id',
            'products' => 'required|array|min:1',
            'payment_method' => [
                'required',
                'string',
                Rule::in([
                    PaymentMethodEnum::CreditCard(),
                ]),
            ],
            'meta' => 'nullable|json',
        ];
    }
}
