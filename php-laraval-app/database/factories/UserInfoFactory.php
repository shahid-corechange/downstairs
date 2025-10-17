<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\UserInfo>
 */
class UserInfoFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'avatar' => null,
            'language' => 'sv_SE',
            'timezone' => 'Europe/Stockholm',
            'currency' => 'SEK',
            'marketing' => 0,
        ];
    }
}
