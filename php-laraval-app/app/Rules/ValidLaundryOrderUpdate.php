<?php

namespace App\Rules;

use App\Models\LaundryOrder;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidLaundryOrderUpdate implements ValidationRule
{
    public function __construct(
        public ?array $allowStatus = [],
    ) {
    }

    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        /** @var LaundryOrder $laundryOrder */
        $laundryOrder = request('laundryOrder');

        if (! in_array($laundryOrder->status, $this->allowStatus)) {
            $fail('unable to update')->translate();
        }
    }
}
