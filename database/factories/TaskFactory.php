<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Task>
 */
class TaskFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(4),
            'description' => fake()->paragraph(2),
            'created_by' => User::factory(),
            'assigned_to' => User::factory(),
            'due_date' => fake()->dateTimeBetween('+1 day', '+1 month')->format('Y-m-d'),
            'status' => 'pending',
        ];
    }
}