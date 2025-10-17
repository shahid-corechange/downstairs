<?php

namespace App\DTOs\Invoice;

use App\DTOs\BaseData;
use App\Enums\Product\ProductUnitEnum;
use App\Enums\VatNumbersEnum;
use Illuminate\Validation\Rule;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\Validation\Rule as SpatieRule;
use Spatie\LaravelData\Mappers\CamelCaseMapper;

#[MapInputName(CamelCaseMapper::class)]
class UpdateInvoiceRowRequestDTO extends BaseData
{
    public function __construct(
        #[SpatieRule('required|integer')]
        public int $parent_id,
        public ?int $id,
        public string $type,
        public ?string $description,
        public float $quantity,
        public ?string $unit,
        public float $price,
        #[SpatieRule('required|numeric|min:0|max:100')]
        public int $discount_percentage,
        public int $vat,
        #[SpatieRule('required|boolean')]
        public bool $has_rut,
    ) {
    }

    public static function rules(): array
    {
        return [
            'id' => 'nullable|integer',
            'type' => 'string|in:fixed price,order',
            'description' => 'nullable|string',
            'quantity' => 'required|numeric',
            'unit' => ['nullable', Rule::in(ProductUnitEnum::values())],
            'price' => 'required|numeric',
            'vat' => ['required', Rule::in(VatNumbersEnum::values())],
        ];
    }
}
