<?php

namespace Database\Factories;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Expense>
 */
class ExpenseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'description' => fake()->sentence(),
            'price' => fake()->randomFloat(),
            'date' => fake()->dateTimeBetween(Carbon::now()->subDays(30), Carbon::now())->format('Y-m-d H:i:s'),
            'user_id' => User::factory(),
        ];
    }
}
