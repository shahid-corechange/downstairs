<?php

namespace App\DTOs\Product;

use App\DTOs\BaseData;
use App\DTOs\Translation\UpdateDefaultTranslationRequestDTO;
use App\Enums\Product\ProductTypeEnum;
use App\Enums\VatNumbersEnum;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\Rule;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Mappers\CamelCaseMapper;
use Spatie\LaravelData\Optional;

#[MapInputName(CamelCaseMapper::class)]
class UpdateProductRequestDTO extends BaseData
{
    public function __construct(
        public UpdateDefaultTranslationRequestDTO $name,
        public UpdateDefaultTranslationRequestDTO $description,
        public array $category_ids,
        public array|Optional $service_ids,
        public array|Optional $addon_ids,
        public array|Optional $store_ids,
        public string|Optional $type,
        public float|Optional $price,
        public int|Optional $credit_price,
        public int|Optional $vat_group,
        public bool|Optional $has_rut,
        public UploadedFile|Optional $thumbnail,
        public string|Optional $color,
    ) {
    }

    public static function rules(): array
    {
        return [
            'category_ids' => 'array|min:1',
            'category_ids.*' => 'numeric|exists:categories,id',
            'service_ids' => 'array',
            'service_ids.*' => 'numeric|exists:services,id',
            'addon_ids' => 'array',
            'addon_ids.*' => 'numeric|exists:addons,id',
            'store_ids' => 'array',
            'store_ids.*' => 'numeric|exists:stores,id',
            'type' => [Rule::in(ProductTypeEnum::values())],
            'price' => 'numeric|min:0',
            'credit_price' => 'numeric|min:0',
            'vat_group' => [Rule::in(VatNumbersEnum::values())],
            'has_rut' => 'boolean',
            'thumbnail' => 'image|max:5120|mimes:svg',
            'color' => 'string',
        ];
    }
}
