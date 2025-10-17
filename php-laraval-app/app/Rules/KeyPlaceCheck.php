<?php

namespace App\Rules;

use App\Models\KeyPlace;
use App\Models\Property;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class KeyPlaceCheck implements ValidationRule
{
    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value) {
            /** @var Property|null $property */
            $property = request()->route('property');
            $keyPlace = KeyPlace::find($value);

            if (! $keyPlace) {
                $fail('key place not found')->translate();
            } elseif ((! $property && $keyPlace->property_id) ||
                ($property && $keyPlace->property_id && $property->id !== $keyPlace->property_id)) {
                $fail('key place already taken')->translate();
            }
        }
    }
}
