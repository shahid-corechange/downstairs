<?php

namespace Database\Factories;

use App\Enums\LeaveRegistration\AbsenceTypeEnum;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class LeaveRegistrationFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        $user = User::role('Worker')->inRandomOrder()->first();
        $start_at = fake()->dateTimeBetween('now', '+3 days');

        return [
            'employee_id' => $user->employee->id,
            'type' => fake()->randomElement(AbsenceTypeEnum::values()),
            'start_at' => $start_at,
            'end_at' => Carbon::parse($start_at)->addDays(fake()->numberBetween(1, 5)),
        ];
    }
}
