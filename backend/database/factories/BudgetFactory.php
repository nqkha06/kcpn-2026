<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Budget>
 */
class BudgetFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'category_id' => Category::factory(),
            'amount_limit' => fake()->randomFloat(2, 1_000_000, 20_000_000),
            'period' => fake()->randomElement(['monthly', 'yearly']),
            'status' => fake()->randomElement(['active', 'active', 'inactive']),
            'note' => fake()->optional()->randomElement([
                'Theo dõi chi tiêu định kỳ.',
                'Giới hạn để giữ dòng tiền ổn định.',
                'Ngân sách thử nghiệm cho tháng này.',
            ]),
        ];
    }

    public function monthly(): static
    {
        return $this->state(fn (array $attributes): array => [
            'period' => 'monthly',
        ]);
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => 'active',
        ]);
    }
}
