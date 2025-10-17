<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class BlockDayFactory extends Factory
{
    public function definition(): array
    {
        return [
            'block_date' => fake()->dateTimeThisMonth('+6 months')->format('Y-m-d'),
            'start_block_time' => '00:00:00',
            'end_block_time' => '23:59:59',
        ];
    }
}
