<?php

namespace App\Rules;

use Carbon\Carbon;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class DateAfterNow implements ValidationRule
{
    public function __construct(
        private string|Carbon|null $originalDate = null,
    ) {
    }

    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $datetime = Carbon::create($value);

        // Skip the validation if the original date is the same as the new date.
        if ($this->originalDate) {
            $carbon = Carbon::create($this->originalDate);

            if ($carbon->toISOString() === $datetime->toISOString()) {
                return;
            }
        }

        if ($datetime->format('Y-m-d') < now()->format('Y-m-d')) {
            $fail(__('date after now validation message'));
        }
    }
}
