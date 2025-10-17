<?php

namespace App\DTOs\Addon;

use App\DTOs\BaseData;
use App\DTOs\CustomTask\CreateCustomTaskRequestDTO;
use App\DTOs\Translation\CreateDefaultTranslationRequestDTO;
use App\Enums\Product\ProductUnitEnum;
use App\Enums\VatNumbersEnum;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\Rule;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\Mappers\CamelCaseMapper;
use Spatie\LaravelData\Optional;

#[MapInputName(CamelCaseMapper::class)]
class CreateAddonRequestDTO extends BaseData
{
    public function __construct(
        public CreateDefaultTranslationRequestDTO $name,
        public CreateDefaultTranslationRequestDTO $description,
        public array $service_ids,
        public array $category_ids,
        public array|Optional $product_ids,
        public string $unit,
        public float $price,
        public int $credit_price,
        public int $vat_group,
        public bool $has_rut,
        public UploadedFile|Optional $thumbnail,
        public string $color,
        #[DataCollectionOf(CreateCustomTaskRequestDTO::class)]
        public DataCollection $tasks,
    ) {
    }

    public static function rules(): array
    {
        return [
            'name' => 'required',
            'description' => 'required',
            'service_ids' => 'required|array|min:1',
            'service_ids.*' => 'required|numeric|exists:services,id',
            'category_ids' => 'required|array|min:1',
            'category_ids.*' => 'required|numeric|exists:categories,id',
            'product_ids' => 'array',
            'product_ids.*' => 'numeric|exists:products,id',
            'unit' => ['required', 'string', Rule::in(ProductUnitEnum::values())],
            'price' => 'required|numeric|min:0',
            'credit_price' => 'required|numeric|min:0',
            'vat_group' => [Rule::in(VatNumbersEnum::values())],
            'has_rut' => 'required|boolean',
            'thumbnail' => 'image|max:5120|mimes:svg',
            'color' => 'required|string',
            'tasks' => 'required|array|min:1',
        ];
    }
}
