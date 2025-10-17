<?php

namespace App\Rules;

use App\Models\Schedule;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidLaundrySchedule implements ValidationRule
{
    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $userId = request('userId');
        $schedule = Schedule::where('id', $value)->where('user_id', $userId)->first();

        if (! $schedule) {
            $fail('schedule does not exists')->translate();
        }
    }
}
