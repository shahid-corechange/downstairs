<?php

namespace App\Rules;

use App\Models\Store;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class CashierInStore implements ValidationRule
{
    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $storeId = request()->session()->get('store_id');
        $store = Store::find($storeId);

        if (! $store->users->contains('id', $value)) {
            $fail('cashier not in store')->translate();
        }
    }
}
