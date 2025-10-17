<?php

namespace App\DTOs\LaundryPreference;

use App\DTOs\BaseData;
use App\DTOs\Translation\CreateDefaultTranslationRequestDTO;
use App\Enums\VatNumbersEnum;
use Illuminate\Validation\Rule;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Mappers\CamelCaseMapper;

#[MapInputName(CamelCaseMapper::class)]
class CreateLaundryPreferenceRequestDTO extends BaseData
{
    public function __construct(
        public CreateDefaultTranslationRequestDTO $name,
        public CreateDefaultTranslationRequestDTO $description,
        public float $price,
        public int $vat_group,
    ) {
    }

    public static function rules(): array
    {
        return [
            'name' => 'required',
            'description' => 'required',
            'price' => 'required|numeric|min:0',
            'vat_group' => [Rule::in(VatNumbersEnum::values())],
        ];
    }
}
