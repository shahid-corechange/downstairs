<?php

namespace App\DTOs\LaundryPreference;

use App\DTOs\BaseData;
use App\DTOs\Translation\UpdateDefaultTranslationRequestDTO;
use App\Enums\VatNumbersEnum;
use Illuminate\Validation\Rule;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Mappers\CamelCaseMapper;
use Spatie\LaravelData\Optional;

#[MapInputName(CamelCaseMapper::class)]
class UpdateLaundryPreferenceRequestDTO extends BaseData
{
    public function __construct(
        public UpdateDefaultTranslationRequestDTO $name,
        public UpdateDefaultTranslationRequestDTO $description,
        public float|Optional $price,
        public int|Optional $vat_group,
    ) {
    }

    public static function rules(): array
    {
        return [
            'price' => 'numeric|min:0',
            'vat_group' => [Rule::in(VatNumbersEnum::values())],
        ];
    }
}
