<?php

namespace App\Rules;

use App\Models\GlobalSetting;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Str;

class GlobalSettingKey implements ValidationRule
{
    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $result = GlobalSetting::where('key', strtoupper(Str::snake($value)))->first();
        if ($result) {
            $fail('already exists')->translate();
        }
    }
}
