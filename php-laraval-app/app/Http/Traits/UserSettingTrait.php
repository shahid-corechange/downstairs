<?php

namespace App\Http\Traits;

use App\Enums\SettingTypeEnum;
use App\Exceptions\CustomValidationException;
use App\Models\User;
use Str;

trait UserSettingTrait
{
    /**
     * Create default user settings.
     */
    private function createDefaultSettings(User $user): void
    {
        $settings = [
            ['key' => 'push_notification', 'value' => 'true', 'type' => SettingTypeEnum::Boolean],
            ['key' => 'email_notification', 'value' => 'false', 'type' => SettingTypeEnum::Boolean],
            ['key' => 'sms_notification', 'value' => 'false', 'type' => SettingTypeEnum::Boolean],
        ];
        $user->settings()->createMany($settings);
    }

    /**
     * Cast to array with original value.
     */
    private function castValues(array $settings, bool $keyToCamel = false): array
    {
        return array_reduce($settings, function ($carry, $item) use ($keyToCamel) {
            $key = $keyToCamel ? Str::camel($item['key']) : $item['key'];
            $carry[$key] = $this->transform($item['value'], $item['type']);

            return $carry;
        }, []);
    }

    /**
     * Transform value to its original type.
     *
     * @return mixed
     */
    protected function transform(string $value, string $type)
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

    private function getData(): array
    {
        $results = [];
        foreach (request()->input() as $key => $value) {
            $results[] = $this->transformPayload($key, $value);
        }

        return $results;
    }

    /**
     * Transform payload to array.
     */
    protected function transformPayload(string $key, mixed $value): array
    {
        // validation not allowed array or object
        if (is_array($value)) {
            throw new CustomValidationException(
                __('value must not array or object'),
                [$key => __('value must not array or object')]
            );
        }

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
            'key' => Str::snake($key),
            'value' => strval($value),
            'type' => $type,
        ];
    }

    /**
     * Update user settings.
     * Create new data or update existing data.
     */
    public function updateSettings(User $user, array $data): void
    {
        foreach ($data as $item) {
            $user->settings()->updateOrCreate(['key' => $item['key']], $item);
        }
    }
}
