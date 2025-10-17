<?php

namespace App\DTOs\Setting;

use App\DTOs\BaseData;
use App\Rules\SettingValue;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Mappers\CamelCaseMapper;

#[MapInputName(CamelCaseMapper::class)]
class UpdateSystemSettingRequestDTO extends BaseData
{
    public function __construct(
        public string $key,
        public mixed $value,
    ) {
    }

    public static function rules(): array
    {
        $key = request()->get('key');

        return [
            'key' => 'required|string|max:255,exists:global_settings,key',
            'value' => [new SettingValue($key)],
        ];
    }
}
