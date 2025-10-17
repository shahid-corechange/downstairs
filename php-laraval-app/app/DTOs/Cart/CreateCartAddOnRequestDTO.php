<?php

namespace App\DTOs\Cart;

use App\DTOs\BaseData;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\Validation\Rule;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\Mappers\CamelCaseMapper;
use Spatie\LaravelData\Optional;

#[MapInputName(CamelCaseMapper::class)]
class CreateCartAddOnRequestDTO extends BaseData
{
    public function __construct(
        #[Rule('required|numeric|exists:addons,id')]
        public int $addon_id,
        #[Rule('required|numeric|exists:schedules,id')]
        public int $schedule_id,
        public float $quantity,
        #[DataCollectionOf(CreateCartProductRequestDTO::class)]
        public DataCollection|Optional $products,
    ) {
    }

    public static function rules(): array
    {
        return [
            'quantity' => 'required|numeric|gt:0',
        ];
    }
}
