<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class SubscriptionStaffDetailsFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        //$user = User::all()->random();
        $user = User::role(['Employee', 'Worker'])->get()->random();

        return [
            'user_id' => $user->id,
            'quarters' => fake()->numberBetween(4, 16),
        ];
    }
}
