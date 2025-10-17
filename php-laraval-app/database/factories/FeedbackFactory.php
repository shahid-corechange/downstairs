<?php

namespace Database\Factories;

use App\Models\Feedback;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Adress>
 */
class FeedbackFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Feedback::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'feedbackable_id' => $this->faker->numberBetween(1, 10),
            'feedbackable_type' => User::class,
            'option' => $this->faker->randomElement($this->getOptions()),
            'description' => $this->faker->paragraph,
        ];
    }

    private function getOptions()
    {
        return [
            'Function suggestion',
            'Technical',
            'Issues',
        ];
    }
}
