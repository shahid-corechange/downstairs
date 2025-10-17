<?php

namespace App\DTOs\OrderRow;

use App\DTOs\BaseData;
use App\Enums\Product\ProductUnitEnum;
use App\Enums\VatNumbersEnum;
use App\Transformers\StringTransformer;
use Illuminate\Validation\Rule;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\WithTransformer;
use Spatie\LaravelData\Mappers\CamelCaseMapper;
use Spatie\LaravelData\Optional;

#[MapInputName(CamelCaseMapper::class)]
class CreateOrderRowRequestDTO extends BaseData
{
    public function __construct(
        #[WithTransformer(StringTransformer::class)]
        public string $description,
        public float $quantity,
        public string $unit,
        public float $price,
        public int|Optional $discount_percentage,
        public int $vat,
        public bool $has_rut,
        #[WithTransformer(StringTransformer::class)]
        public string|null|Optional $internal_note,
    ) {
    }

    public static function rules(): array
    {
        return [
            'description' => 'required|string',
            'quantity' => 'required|numeric|min:0',
            'unit' => ['required', 'string', Rule::in(ProductUnitEnum::values())],
            'price' => 'required|numeric|min:0',
            'discount_percentage' => 'numeric|min:0',
            'vat' => ['required', 'numeric', Rule::in(VatNumbersEnum::values())],
            'has_rut' => 'required|boolean',
            'internal_note' => 'nullable|string',
        ];
    }
}
