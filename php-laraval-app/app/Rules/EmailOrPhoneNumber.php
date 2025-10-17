<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Validator;

class EmailOrPhoneNumber implements ValidationRule
{
    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Validate as email
        $emailValidator = Validator::make([$attribute => $value], [$attribute => 'email']);

        // Validate as phone number
        $phoneValidator = Validator::make([$attribute => $value], [$attribute => 'regex:/^\d{10,}$/']);

        if (! $emailValidator->passes() && ! $phoneValidator->passes()) {
            $fail('must be a valid email address or phone number')->translate();
        }
    }
}
