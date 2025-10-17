<?php

namespace App\Rules;

use Carbon\Carbon;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class TimeAfterNow implements ValidationRule
{
    public function __construct(
        private ?string $date = null,
        private string|Carbon|null $originalDateTime = null,
    ) {
    }

    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $datetime = $this->date ? Carbon::create($this->date.$value) :
                now()->setTimeFromTimeString($value);

        // Skip the validation if the original date time is the same as the new date time.
        if ($this->originalDateTime) {
            $carbon = Carbon::create($this->originalDateTime);

            if ($carbon->toISOString() === $datetime->toISOString()) {
                return;
            }
        }

        if ($datetime->isPast()) {
            $fail(__('time after now validation message'));
        }
    }
}
