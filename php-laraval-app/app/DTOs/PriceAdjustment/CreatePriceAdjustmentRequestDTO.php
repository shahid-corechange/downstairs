<?php

namespace App\DTOs\PriceAdjustment;

use App\DTOs\BaseData;
use App\Enums\PriceAdjustment\PriceAdjustmentPriceTypeEnum;
use App\Enums\PriceAdjustment\PriceAdjustmentTypeEnum;
use App\Transformers\StringTransformer;
use Illuminate\Validation\Rule;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\WithTransformer;
use Spatie\LaravelData\Mappers\CamelCaseMapper;

#[MapInputName(CamelCaseMapper::class)]
class CreatePriceAdjustmentRequestDTO extends BaseData
{
    public function __construct(
        public string $type,
        #[WithTransformer(StringTransformer::class)]
        public ?string $description,
        public string $price_type,
        public float $price,
        public string $execution_date,
        public array $row_ids,
    ) {
    }

    public static function rules(): array
    {
        return [
            'type' => ['required', 'string', Rule::in(PriceAdjustmentTypeEnum::values())],
            'description' => 'nullable|string',
            'price_type' => ['required', 'string', Rule::in(PriceAdjustmentPriceTypeEnum::values())],
            'price' => 'required|numeric',
            'execution_date' => 'required|date|date_format:Y-m-d|after:today',
            'row_ids' => 'required|array|min:1',
            'row_ids.*' => 'required|numeric|'.self::rowIdValidation(request('type')),
        ];
    }

    /**
     * Validation row ids based on type.
     */
    private static function rowIdValidation(string $type): string
    {
        return "exists:{$type}s,id";
    }
}
