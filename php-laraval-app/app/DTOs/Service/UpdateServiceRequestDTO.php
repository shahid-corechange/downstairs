<?php

namespace App\DTOs\Service;

use App\DTOs\BaseData;
use App\DTOs\Translation\UpdateDefaultTranslationRequestDTO;
use App\Enums\MembershipTypeEnum;
use App\Enums\Service\ServiceTypeEnum;
use App\Enums\VatNumbersEnum;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\Rule;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Mappers\CamelCaseMapper;
use Spatie\LaravelData\Optional;

#[MapInputName(CamelCaseMapper::class)]
class UpdateServiceRequestDTO extends BaseData
{
    public function __construct(
        public UpdateDefaultTranslationRequestDTO $name,
        public UpdateDefaultTranslationRequestDTO $description,
        public array|Optional $addon_ids,
        public array|Optional $category_ids,
        public array|Optional $product_ids,
        public string|Optional $type,
        public string|Optional $membership_type,
        public float|Optional $price,
        public int|Optional $vat_group,
        public bool|Optional $has_rut,
        public UploadedFile|Optional $thumbnail,
    ) {
    }

    public static function rules(): array
    {
        return [
            'addon_ids' => 'array',
            'addon_ids.*' => 'numeric|exists:addons,id',
            'category_ids' => 'array|min:1',
            'category_ids.*' => 'numeric|exists:categories,id',
            'product_ids' => 'array',
            'product_ids.*' => 'numeric|exists:products,id',
            'type' => [Rule::in(ServiceTypeEnum::values())],
            'membership_type' => [Rule::in(MembershipTypeEnum::values())],
            'price' => 'numeric|min:0',
            'vat_group' => [Rule::in(VatNumbersEnum::values())],
            'has_rut' => 'boolean',
            'thumbnail' => 'image|max:5120',
        ];
    }
}
