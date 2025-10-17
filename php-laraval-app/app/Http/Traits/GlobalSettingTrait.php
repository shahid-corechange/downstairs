<?php

namespace App\Http\Traits;

use App\Enums\SettingTypeEnum;

trait GlobalSettingTrait
{
    /**
     * Cast to array with original value.
     */
    private function castValues(array $settings): array
    {
        return array_reduce($settings, function ($carry, $item) {
            $carry[$item['key']] = $this->transform($item['value'], $item['type']);

            return $carry;
        }, []);
    }

    /**
     * Transform value to its original type.
     *
     * @return mixed
     */
    public static function transform(string $value, string $type)
    {
        switch ($type) {
            case SettingTypeEnum::Integer():
                return intval($value);
            case SettingTypeEnum::Boolean():
                return $value == 'true';
            case SettingTypeEnum::Float():
                return floatval($value);
            default:
                return $value;
        }
    }

    /**
     * Transform payload to array.
     */
    private function transformPayload(string $key, mixed $value): array
    {
        $type = SettingTypeEnum::String();

        if (is_int($value)) {
            $type = SettingTypeEnum::Integer();
        } elseif (is_bool($value)) {
            $type = SettingTypeEnum::Boolean();
            $value = $value ? 'true' : 'false';
        } elseif (is_float($value)) {
            $type = SettingTypeEnum::Float();
        }

        return [
            'key' => strtoupper($key),
            'value' => strval($value),
            'type' => $type,
        ];
    }

    /**
     * Transform value.
     */
    private function transformValue(mixed $value, string $type): string
    {
        if ($type == SettingTypeEnum::Boolean()) {
            return $value ? 'true' : 'false';
        } else {
            return strval($value);
        }
    }
}
