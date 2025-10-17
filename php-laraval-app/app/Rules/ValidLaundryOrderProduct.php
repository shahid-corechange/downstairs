<?php

namespace App\Rules;

use App\Models\Product;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidLaundryOrderProduct implements ValidationRule
{
    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value === 0) {
            return;
        }

        $product = Product::find($value);

        if (! $product) {
            $fail('product does not exists')->translate();
        }
    }
}
