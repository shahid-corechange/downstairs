<?php

namespace App\Rules;

use App\Models\ScheduleEmployee;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class AvailableScheduleAdjustment implements ValidationRule
{
    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $scheduleEmployee = ScheduleEmployee::find($value);

        if (! $scheduleEmployee) {
            $fail('Schedule employee not found')->translate();
        } elseif ($scheduleEmployee->timeAdjustment) {
            $fail('Already has a time adjustment')->translate();
        }
    }
}
