<?php

namespace App\DTOs\Property;

use App\DTOs\BaseData;
use App\Rules\KeyPlaceCheck;
use App\Transformers\StringTransformer;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\Validation\Rule;
use Spatie\LaravelData\Attributes\WithTransformer;
use Spatie\LaravelData\Mappers\CamelCaseMapper;
use Spatie\LaravelData\Optional;

#[MapInputName(CamelCaseMapper::class)]
class KeyInformationRequestDTO extends BaseData
{
    public function __construct(
        #[WithTransformer(StringTransformer::class)]
        #[Rule(['nullable', 'string', new KeyPlaceCheck()])]
        public string|null|Optional $key_place,
        #[WithTransformer(StringTransformer::class)]
        public string|null|Optional $front_door_code,
        #[WithTransformer(StringTransformer::class)]
        public string|null|Optional $alarm_code_off,
        #[WithTransformer(StringTransformer::class)]
        public string|null|Optional $alarm_code_on,
        #[WithTransformer(StringTransformer::class)]
        public string|null|Optional $information,
    ) {
    }

    public static function rules(): array
    {
        return [
            // 'key_place' => ['nullable', 'string', new KeyPlaceCheck()],
            'front_door_code' => 'nullable|string',
            'alarm_code_off' => 'nullable|string',
            'alarm_code_on' => 'nullable|string',
            'information' => 'nullable|string',
        ];
    }
}
