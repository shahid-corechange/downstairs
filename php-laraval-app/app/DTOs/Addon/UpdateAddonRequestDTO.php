<?php

namespace App\DTOs\Addon;

use App\DTOs\BaseData;
use App\DTOs\Translation\UpdateDefaultTranslationRequestDTO;
use App\Enums\VatNumbersEnum;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\Rule;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Mappers\CamelCaseMapper;
use Spatie\LaravelData\Optional;

#[MapInputName(CamelCaseMapper::class)]
class UpdateAddonRequestDTO extends BaseData
{
    public function __construct(
        public UpdateDefaultTranslationRequestDTO $name,
        public UpdateDefaultTranslationRequestDTO $description,
        public array|Optional $service_ids,
        public array|Optional $category_ids,
        public array|Optional $product_ids,
        public string|Optional $unit,
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
            'service_ids' => 'array|min:1',
            'service_ids.*' => 'numeric|exists:services,id',
            'category_ids' => 'array|min:1',
            'category_ids.*' => 'numeric|exists:categories,id',
            'product_ids' => 'array',
            'product_ids.*' => 'numeric|exists:products,id',
            'unit' => 'string',
            'price' => 'numeric|min:0',
            'credit_price' => 'numeric|min:0',
            'vat_group' => [Rule::in(VatNumbersEnum::values())],
            'has_rut' => 'boolean',
            'thumbnail' => 'image|max:5120|mimes:svg',
            'color' => 'string',
        ];
    }
}
