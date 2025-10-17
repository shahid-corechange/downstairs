<?php

namespace App\Rules;

use App\Models\GlobalSetting;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class SettingValue implements ValidationRule
{
    public function __construct(public string $key)
    {
    }

    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $setting = GlobalSetting::where('key', $this->key)->first();

        if (! $setting) {
            $fail(__('not valid key'));
        } elseif ($setting->type !== gettype($value)) {
            $fail(__('not valid value, type of value must be', ['type' => $setting->type]));
        }
    }
}
