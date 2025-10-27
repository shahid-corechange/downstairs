<?php

namespace App\Rules;

use App\Models\ScheduleEmployee;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class WorkerNotAssignToSchedule implements ValidationRule
{
    public function __construct(
        private int $schedule_id,
    ) {
    }

    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $scheduleEmployee = ScheduleEmployee::where('schedule_id', $this->schedule_id)
            ->where('user_id', $value)
            ->first();

        if ($scheduleEmployee) {
            $fail('worker already assign to schedule')->translate();
        }
    }
}
