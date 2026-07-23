<?php

namespace Database\Factories;

use App\Models\Menu;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Menu>
 */
class MenuFactory extends Factory
{
    protected $model = Menu::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->words(2, true),
            'url' => '/'.fake()->slug(),
            'parent_id' => null,
            'canonical' => fake()->randomElement(['home.header', 'home.footer', 'user.header']),
            'sort_order' => fake()->numberBetween(0, 20),
            'target' => '_self',
            'status' => 'active',
        ];
    }

    public function header(): static
    {
        return $this->state(fn (array $attributes): array => [
            'canonical' => 'home.header',
        ]);
    }

    public function footer(): static
    {
        return $this->state(fn (array $attributes): array => [
            'canonical' => 'home.footer',
        ]);
    }

    public function userHeader(): static
    {
        return $this->state(fn (array $attributes): array => [
            'canonical' => 'user.header',
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => 'inactive',
        ]);
    }
}
