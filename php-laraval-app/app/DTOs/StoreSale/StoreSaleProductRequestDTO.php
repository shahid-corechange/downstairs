<?php

namespace App\DTOs\StoreSale;

use App\DTOs\BaseData;
use App\Rules\ValidStoreProduct;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\Validation\Rule;
use Spatie\LaravelData\Mappers\CamelCaseMapper;
use Spatie\LaravelData\Optional;

#[MapInputName(CamelCaseMapper::class)]
class StoreSaleProductRequestDTO extends BaseData
{
    public function __construct(
        #[Rule('required', 'numeric', new ValidStoreProduct())]
        public int $product_id,
        #[Rule('required|string')]
        public string $name,
        #[Rule('nullable', 'string')]
        public string|null|Optional $note,
        #[Rule('required', 'numeric', 'min:1')]
        public int $quantity,
        #[Rule('required', 'numeric', 'min:0')]
        public float $price,
        #[Rule('required', 'numeric', 'in:0,6,12,25')]
        public int $vat_group,
        #[Rule('required', 'numeric', 'min:0|max:100')]
        public float $discount,
        #[Rule('required|boolean')]
        public bool $is_modified,
    ) {
    }
}
