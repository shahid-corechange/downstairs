<?php

namespace App\DTOs\Cart;

use App\DTOs\BaseData;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\Validation\Rule;
use Spatie\LaravelData\Mappers\CamelCaseMapper;

#[MapInputName(CamelCaseMapper::class)]
class CreateCartProductRequestDTO extends BaseData
{
    public function __construct(
        #[Rule('required|numeric|exists:products,id')]
        public int $product_id,
        #[Rule('required|numeric|exists:schedules,id')]
        public int $schedule_id,
        #[Rule('required|numeric|gt:0')]
        public float $quantity,
    ) {
    }
}
