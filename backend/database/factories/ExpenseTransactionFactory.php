<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\User;
use App\Models\UserWallet;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ExpenseTransaction>
 */
class ExpenseTransactionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $type = fake()->randomElement(['income', 'expense']);
        $amountRange = $type === 'income' ? [500_000, 25_000_000] : [20_000, 3_500_000];

        return [
            'user_id' => User::factory(),
            'wallet_id' => fn (array $attributes): int => UserWallet::factory()
                ->create(['user_id' => $attributes['user_id']])
                ->id,
            'category_id' => Category::factory(),
            'type' => $type,
            'amount' => fake()->randomFloat(2, $amountRange[0], $amountRange[1]),
            'transacted_at' => fake()->dateTimeBetween('-6 months', 'now')->format('Y-m-d'),
            'status' => fake()->randomElement(['posted', 'posted', 'posted', 'pending']),
            'note' => $type === 'income'
                ? fake()->randomElement(['Lương tháng', 'Thưởng dự án', 'Cashback', 'Thu nhập phụ'])
                : fake()->randomElement(['Ăn trưa', 'Cà phê', 'Mua sắm online', 'Thanh toán hóa đơn', 'Di chuyển']),
            'labels' => fake()->boolean(45) ? fake()->randomElements(['work', 'family', 'recurring', 'online', 'urgent'], 2) : null,
        ];
    }

    public function forUser(User $user): static
    {
        return $this->state(function () use ($user): array {
            $walletId = $user->wallets()->inRandomOrder()->value('id')
                ?? UserWallet::factory()->for($user)->create()->id;

            return [
                'user_id' => $user->id,
                'wallet_id' => $walletId,
            ];
        });
    }

    public function income(): static
    {
        return $this->state(fn (array $attributes): array => [
            'type' => 'income',
            'amount' => fake()->randomFloat(2, 500_000, 25_000_000),
            'note' => fake()->randomElement(['Lương tháng', 'Thưởng dự án', 'Cashback', 'Thu nhập phụ']),
        ]);
    }

    public function expense(): static
    {
        return $this->state(fn (array $attributes): array => [
            'type' => 'expense',
            'amount' => fake()->randomFloat(2, 20_000, 3_500_000),
            'note' => fake()->randomElement(['Ăn uống', 'Di chuyển', 'Mua sắm', 'Thanh toán hóa đơn', 'Giải trí']),
        ]);
    }

    public function posted(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => 'posted',
        ]);
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => 'pending',
        ]);
    }
}
