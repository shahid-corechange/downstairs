<?php

namespace App\DTOs\CustomerDiscount;

use App\DTOs\BaseData;
use App\Enums\Discount\CustomerDiscountTypeEnum;
use Illuminate\Validation\Rule;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Mappers\CamelCaseMapper;
use Spatie\LaravelData\Optional;

#[MapInputName(CamelCaseMapper::class)]
class CreateCustomerDiscountRequestDTO extends BaseData
{
    public function __construct(
        public int $user_id,
        public string $type,
        public int $value,
        public null|Optional|string $start_date,
        public null|Optional|string $end_date,
        public null|Optional|int $usage_limit,
    ) {
    }

    public static function rules(): array
    {
        return [
            'user_id' => 'required|numeric|exists:users,id',
            'type' => ['required', Rule::in(CustomerDiscountTypeEnum::values())],
            'value' => 'required|numeric|min:1|max:100',
            'startDate' => 'nullable|date',
            'endDate' => 'nullable|date|after:startDate',
            'usageLimit' => 'nullable|numeric|min:1',
        ];
    }
}
