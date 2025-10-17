<?php

namespace App\DTOs\FixedPriceRow;

use App\DTOs\BaseData;
use App\Rules\FixedPriceRowTypeValidation;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\Validation\Rule;
use Spatie\LaravelData\Mappers\CamelCaseMapper;

#[MapInputName(CamelCaseMapper::class)]
class CreateFixedPriceRowRequestDTO extends BaseData
{
    public function __construct(
        #[Rule(['required', new FixedPriceRowTypeValidation()])]
        public string $type,
        public int $quantity,
        public float $price,
        public ?int $vat_group,
        public ?array $laundry_product_ids,
    ) {
    }

    // some rule need to be define here to make it work
    public static function rules(): array
    {
        return [
            'quantity' => 'required|numeric|gt:0',
            'price' => 'required|numeric|gt:0',
            'vat_group' => 'nullable|numeric',
            'laundry_product_ids' => 'nullable|array',
            'laundry_product_ids.*' => 'numeric|exists:products,id',
        ];
    }
}
