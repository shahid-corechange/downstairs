<?php

namespace App\Rules;

use App\Models\Product;
use App\Models\ScheduleItem;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ExistScheduleProduct implements ValidationRule
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
        $scheduleProduct = ScheduleItem::where('schedule_id', $this->schedule_id)
            ->where('itemable_id', $value)
            ->where('itemable_type', Product::class)
            ->first();

        if (! $scheduleProduct) {
            $fail('schedule product id not exist')->translate();
        }
    }
}
