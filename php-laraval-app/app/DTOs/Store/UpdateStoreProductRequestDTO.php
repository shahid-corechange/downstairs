<?php

namespace App\DTOs\Store;

use App\DTOs\BaseData;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Mappers\CamelCaseMapper;

#[MapInputName(CamelCaseMapper::class)]
class UpdateStoreProductRequestDTO extends BaseData
{
    public function __construct(
        public array $product_ids,
    ) {
    }

    public static function rules(): array
    {
        return [
            'product_ids' => 'array|min:1',
            'product_ids.*' => 'numeric|exists:products,id',
        ];
    }
}
