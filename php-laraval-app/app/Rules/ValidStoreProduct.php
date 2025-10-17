<?php

namespace App\Rules;

use App\Enums\Store\StoreProductStatusEnum;
use App\Models\StoreProduct;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidStoreProduct implements ValidationRule
{
    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value === 0) {
            return;
        }

        $storeId = request()->session()->get('store_id');
        $storeProduct = StoreProduct::where('product_id', $value)->where('store_id', $storeId)->first();

        if (! $storeProduct) {
            $fail('product does not exists')->translate();
        } elseif ($storeProduct->status !== StoreProductStatusEnum::Active()) {
            $fail('product is not available')->translate();
        }
    }
}
