<?php

namespace App\Rules;

use App\Models\Addon;
use App\Models\ScheduleItem;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ExistScheduleAddon implements ValidationRule
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
        $scheduleAddon = ScheduleItem::where('schedule_id', $this->schedule_id)
            ->where('itemable_id', $value)
            ->where('itemable_type', Addon::class)
            ->first();

        if (! $scheduleAddon) {
            $fail('schedule addon id not exist')->translate();
        }
    }
}
