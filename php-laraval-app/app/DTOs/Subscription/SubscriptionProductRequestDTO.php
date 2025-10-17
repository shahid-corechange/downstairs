<?php

namespace App\DTOs\Subscription;

use App\DTOs\BaseData;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\Validation\Rule;
use Spatie\LaravelData\Mappers\CamelCaseMapper;

#[MapInputName(CamelCaseMapper::class)]
class SubscriptionProductRequestDTO extends BaseData
{
    public function __construct(
        #[Rule('required|numeric|exists:products,id')]
        public int $id,
        #[Rule('required|numeric|gt:0')]
        public float $quantity,
    ) {
    }
}
