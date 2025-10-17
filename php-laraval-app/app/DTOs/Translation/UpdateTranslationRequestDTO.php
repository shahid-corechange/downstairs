<?php

namespace App\DTOs\Translation;

use App\DTOs\BaseData;
use App\Enums\TranslationEnum;
use App\Rules\MetaRule;
use App\Rules\TranslationProperty;
use Illuminate\Validation\Rule;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Mappers\CamelCaseMapper;

#[MapInputName(CamelCaseMapper::class)]
class UpdateTranslationRequestDTO extends BaseData
{
    public function __construct(
        public string $language,
        public array $translations,
    ) {
    }

    public static function rules(): array
    {
        return [
            'language' => ['required', 'string', Rule::in(TranslationEnum::values())],
            'translations' => [new MetaRule()],
            'translations.*' => [new TranslationProperty()],
        ];
    }
}
