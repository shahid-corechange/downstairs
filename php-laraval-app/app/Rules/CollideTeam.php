<?php

namespace App\Rules;

use App\Enums\Schedule\ScheduleStatusEnum;
use App\Models\BlockDay;
use App\Models\Schedule;
use Carbon\Carbon;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Database\Eloquent\Builder;

class CollideTeam implements ValidationRule
{
    public function __construct(
        private string $time,
        private array $excludeScheduleIds = [],
    ) {
    }

    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $startAt = Carbon::parse($this->time);
        $endAt = $startAt->copy()->addMinutes(15);

        $scheduleCount = Schedule::where('team_id', $value)
            ->where('status', '!=', ScheduleStatusEnum::Cancel())
            ->where(function (Builder $query) use ($startAt, $endAt) {
                $query->where('start_at', '<', $endAt)
                    ->orWhere('end_at', '>', $startAt);
            })
            ->whereNotIn('id', $this->excludeScheduleIds)
            ->count();

        $blockDays = BlockDay::where('block_date', '=', $startAt->toDateString())->count();

        if ($scheduleCount > 0) {
            $fail('collide with another schedule')->translate();
        } elseif ($blockDays > 0) {
            $fail('collide with a blocked day')->translate();
        }
    }
}
