<?php

namespace App\DTOs\Product;

use App\DTOs\BaseData;
use App\DTOs\Translation\CreateDefaultTranslationRequestDTO;
use App\Enums\Product\ProductUnitEnum;
use App\Enums\VatNumbersEnum;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\Rule;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Mappers\CamelCaseMapper;
use Spatie\LaravelData\Optional;

#[MapInputName(CamelCaseMapper::class)]
class CreateProductRequestDTO extends BaseData
{
    public function __construct(
        public CreateDefaultTranslationRequestDTO $name,
        public CreateDefaultTranslationRequestDTO $description,
        public array $category_ids,
        public array|Optional $service_ids,
        public array|Optional $addon_ids,
        public array|Optional $store_ids,
        public string $unit,
        public float $price,
        public int $credit_price,
        public int $vat_group,
        public bool $has_rut,
        public UploadedFile|Optional $thumbnail,
        public string $color,
        public array|Optional $meta,
    ) {
    }

    public static function rules(): array
    {
        return [
            'name' => 'required',
            'description' => 'required',
            'category_ids' => 'required|array|min:1',
            'category_ids.*' => 'required|numeric|exists:categories,id',
            'service_ids' => 'array',
            'service_ids.*' => 'numeric|exists:services,id',
            'addon_ids' => 'array',
            'addon_ids.*' => 'numeric|exists:addons,id',
            'store_ids' => 'array',
            'store_ids.*' => 'numeric|exists:stores,id',
            'unit' => ['required', Rule::in(ProductUnitEnum::values())],
            'price' => 'required|numeric|min:0',
            'credit_price' => 'required|numeric|min:0',
            'vat_group' => ['required', Rule::in(VatNumbersEnum::values())],
            'has_rut' => 'required|boolean',
            'thumbnail' => 'image|max:5120|mimes:svg',
            'color' => 'required|string',
        ];
    }
}
