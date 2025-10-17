<?php

namespace App\Rules;

use App\Enums\FixedPrice\FixedPriceRowTypeEnum;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class FixedPriceRowTypeValidation implements ValidationRule
{
    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {

        if (! in_array($value, FixedPriceRowTypeEnum::values())) {
            $fail(__('fixed price row type is not valid'));
        }
    }
}
