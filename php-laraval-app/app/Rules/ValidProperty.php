<?php

namespace App\Rules;

use App\Models\Property;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidProperty implements ValidationRule
{
    public function __construct(
        private string $type,
    ) {
    }

    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $property = Property::where('id', $value)->where('membership_type', $this->type)->first();

        if (! $property) {
            $fail('property does not exists')->translate();
        }
    }
}
