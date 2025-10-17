<?php

namespace App\Rules;

use Arr;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class MetaRule implements ValidationRule
{
    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_array($value) || (count($value) > 0 && ! Arr::isAssoc($value))) {
            $fail('must be an object')->translate();
        }
    }
}
