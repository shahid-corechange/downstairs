<?php

namespace App\Rules;

use App\Models\BlockDay;
use Carbon\Carbon;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class DateNotInBlockDays implements ValidationRule
{
    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $date = Carbon::parse($value);
        $isInBlockDays = BlockDay::where('block_date', $date->format('Y-m-d'))->exists();

        if ($isInBlockDays) {
            $fail('date in block days')->translate();
        }
    }
}
