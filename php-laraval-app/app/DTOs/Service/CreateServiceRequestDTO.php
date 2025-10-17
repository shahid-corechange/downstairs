<?php

namespace App\DTOs\Service;

use App\DTOs\BaseData;
use App\DTOs\CustomTask\CreateCustomTaskRequestDTO;
use App\DTOs\Translation\CreateDefaultTranslationRequestDTO;
use App\Enums\MembershipTypeEnum;
use App\Enums\Service\ServiceTypeEnum;
use App\Enums\VatNumbersEnum;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\Rule;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\Mappers\CamelCaseMapper;
use Spatie\LaravelData\Optional;

#[MapInputName(CamelCaseMapper::class)]
class CreateServiceRequestDTO extends BaseData
{
    public function __construct(
        public CreateDefaultTranslationRequestDTO $name,
        public CreateDefaultTranslationRequestDTO $description,
        public array|Optional $addon_ids,
        public array $category_ids,
        public array|Optional $product_ids,
        public string $type,
        public string $membership_type,
        public float $price,
        public int $vat_group,
        public bool $has_rut,
        public UploadedFile|Optional $thumbnail,
        #[DataCollectionOf(CreateCustomTaskRequestDTO::class)]
        public DataCollection $tasks,
    ) {
    }

    public static function rules(): array
    {
        return [
            'name' => 'required',
            'description' => 'required',
            'addon_ids' => 'array',
            'addon_ids.*' => 'numeric|exists:addons,id',
            'category_ids' => 'required|array|min:1',
            'category_ids.*' => 'required|numeric|exists:categories,id',
            'product_ids' => 'array',
            'product_ids.*' => 'numeric|exists:products,id',
            'type' => ['required', Rule::in(ServiceTypeEnum::values())],
            'membership_type' => ['required', Rule::in(MembershipTypeEnum::values())],
            'price' => 'required|numeric|min:0',
            'vat_group' => ['required', Rule::in(VatNumbersEnum::values())],
            'has_rut' => 'required|boolean',
            'thumbnail' => 'image|max:5120',
            'tasks' => 'required|array|min:1',
        ];
    }
}
