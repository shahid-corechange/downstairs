<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class LaundryDeliveryTime implements ValidationRule
{
    public function __construct()
    {
    }

    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $pickupTime = request('pickupTime');

        if ($pickupTime && $value < $pickupTime) {
            $fail(__('delivery time must be equal or after pickup time'));
        }
    }
}
