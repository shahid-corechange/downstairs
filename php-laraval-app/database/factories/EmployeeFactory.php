<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Customer>
 */
class EmployeeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'fortnox_id' => '3',
            'address_id' => 1,
        ];
    }

    public function forUser(User $user, string $addressId)
    {
        return $this->state(function ($attributes) use ($user, $addressId) {
            return [
                'user_id' => $user->id,
                'address_id' => $addressId,
                'identity_number' => $user->identity_number,
                'name' => "{$user->first_name} {$user->last_name}",
                'email' => $user->email,
                'phone1' => $user->cellphone,
                'dial_code' => $user->dial_code,
            ];
        });
    }
}
