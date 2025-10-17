<?php

namespace App\DTOs\ScheduleCleaningProduct;

use App\DTOs\BaseData;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\Validation\Rule;
use Spatie\LaravelData\Mappers\CamelCaseMapper;

#[MapInputName(CamelCaseMapper::class)]
class CreateProductRequestDTO extends BaseData
{
    public function __construct(
        #[Rule('required|numeric|exists:products,id')]
        public int $product_id,
        public float $quantity,
    ) {
    }

    public static function rules(): array
    {
        return [
            'quantity' => 'required|numeric|gt:0',
        ];
    }
}
