<?php

namespace App\Rules;

use App\Helpers\Validation\SwedishSocialSecurityNumberValidation;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class SwedishSocialSecurityNumber implements ValidationRule
{
    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! SwedishSocialSecurityNumberValidation::validate($value)) {
            $fail('not a valid social security number')->translate();
        }
    }
}
