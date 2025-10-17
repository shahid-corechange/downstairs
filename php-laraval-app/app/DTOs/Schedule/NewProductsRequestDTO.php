<?php

namespace App\DTOs\Schedule;

use App\DTOs\BaseData;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\Validation\Rule;
use Spatie\LaravelData\Mappers\CamelCaseMapper;

#[MapInputName(CamelCaseMapper::class)]
class NewProductsRequestDTO extends BaseData
{
    public function __construct(
        #[Rule('required|numeric|exists:products,id')]
        public int $product_id,
        public float $quantity,
        #[Rule('required|boolean')]
        public bool $use_credit,
    ) {
    }

    // Some validation rules must be defined here in order to make it work
    public static function rules(): array
    {
        return [
            'quantity' => 'required|numeric|gt:0',
        ];
    }
}
