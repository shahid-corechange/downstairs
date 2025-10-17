<?php

namespace Database\Factories;

use App\Enums\ScheduleCleaning\ScheduleCleaningChangeStatusEnum;
use App\Enums\ScheduleCleaning\ScheduleCleaningStatusEnum;
use App\Models\ScheduleCleaning;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Credit>
 */
class ScheduleCleaningChangeRequestFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $scheduleId = ScheduleCleaning::where('status', ScheduleCleaningStatusEnum::Booked())
            ->inRandomOrder()
            ->first()
            ->id;
        $startChange = $this->faker->dateTimeBetween('now', '+1 year');

        return [
            'schedule_cleaning_id' => $scheduleId,
            'start_at_changed' => $startChange,
            'end_at_changed' => Carbon::parse($startChange)->addHours(2),
            'status' => ScheduleCleaningChangeStatusEnum::Pending(),
        ];
    }
}
