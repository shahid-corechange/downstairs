<?php

namespace App\DTOs\LaundryOrderProduct;

use App\DTOs\BaseData;
use App\Rules\ValidLaundryOrderProduct;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\Validation\Rule;
use Spatie\LaravelData\Mappers\CamelCaseMapper;

#[MapInputName(CamelCaseMapper::class)]
class LaundryOrderProductRequestDTO extends BaseData
{
    public function __construct(
        #[Rule('required', 'numeric', new ValidLaundryOrderProduct())]
        public int $product_id,
        #[Rule('required|string')]
        public string $name,
        #[Rule('nullable|string')]
        public ?string $note,
        #[Rule('required|numeric|min:1')]
        public int $quantity,
        #[Rule('required|numeric|min:0')]
        public float $price,
        #[Rule('required|numeric|in:0,6,12,25')]
        public int $vat_group,
        #[Rule('required|numeric|min:0|max:100')]
        public float $discount,
        #[Rule('required|boolean')]
        public bool $has_rut,
        #[Rule('required|boolean')]
        public bool $is_modified,
    ) {
    }
}
