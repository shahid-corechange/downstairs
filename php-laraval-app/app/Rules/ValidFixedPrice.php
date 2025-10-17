<?php

namespace App\Rules;

use App\Models\FixedPrice;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidFixedPrice implements ValidationRule
{
    public function __construct(
        private int $user_id,
    ) {
    }

    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $fixedPrice = FixedPrice::find($value);

        if (! $fixedPrice) {
            $fail('fixed price not exist')->translate();
        } elseif ($fixedPrice->user_id !== $this->user_id) {
            $fail('fixed price not belong to user')->translate();
        }
    }
}
