<?php

namespace App\Rules;

use Carbon\Carbon;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class CreateSubscriptionTime implements ValidationRule
{
    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $frequency = request('frequency');

        if ($frequency && $frequency !== 0) {
            $date = request('startAt');
            $datetime = $date ? Carbon::create($date.$value) : now()->setTimeFromTimeString($value);

            if ($datetime->isPast()) {
                $fail(__('time after now validation message'));
            }
        }
    }
}
