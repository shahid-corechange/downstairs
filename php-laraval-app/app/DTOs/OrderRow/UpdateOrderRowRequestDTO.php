<?php

namespace App\DTOs\OrderRow;

use App\DTOs\BaseData;
use App\Enums\Product\ProductUnitEnum;
use App\Enums\VatNumbersEnum;
use Illuminate\Validation\Rule;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Mappers\CamelCaseMapper;
use Spatie\LaravelData\Optional;

#[MapInputName(CamelCaseMapper::class)]
class UpdateOrderRowRequestDTO extends BaseData
{
    public function __construct(
        public string|Optional $description,
        public float|Optional $quantity,
        public string|Optional $unit,
        public float|Optional $price,
        public int|Optional $discount_percentage,
        public int|Optional $vat,
        public bool|Optional $has_rut,
        public string|null|Optional $internal_note,
    ) {
    }

    public static function rules(): array
    {
        return [
            'description' => 'string',
            'quantity' => 'numeric|min:0',
            'unit' => ['string', Rule::in(ProductUnitEnum::values())],
            'price' => 'numeric|min:0',
            'discount_percentage' => 'numeric|min:0',
            'vat' => ['numeric', Rule::in(VatNumbersEnum::values())],
            'has_rut' => 'boolean',
            'internal_note' => 'nullable|string',
        ];
    }
}
