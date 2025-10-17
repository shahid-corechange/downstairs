<?php

namespace Database\Factories;

use App\Enums\User\UserStatusEnum;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

class UserFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'first_name' => fake()->firstName,
            'last_name' => fake()->lastName,
            'email' => fake()->unique()->safeEmail,
            'email_verified_at' => now(),
            'cellphone' => fake()->unique()->numerify('467########'),
            'dial_code' => '46',
            'cellphone_verified_at' => now(),
            'identity_number' => generate_swedish_ssn(),
            'identity_number_verified_at' => now(),
            'password' => Hash::make('password'),
            'status' => UserStatusEnum::Active(),
        ];
    }

    public function addData(array $data): self
    {

        return $this->state(function (array $attributes) use ($data) {
            return $data;
        });
    }
}
