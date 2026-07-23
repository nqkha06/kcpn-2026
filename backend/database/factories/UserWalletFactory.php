<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\UserWallet;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<UserWallet>
 */
class UserWalletFactory extends Factory
{
    protected $model = UserWallet::class;

    public function definition(): array
    {
        $wallets = [
            ['name' => 'Ví tiền mặt', 'balance' => [200_000, 3_000_000]],
            ['name' => 'Tài khoản ngân hàng', 'balance' => [2_000_000, 35_000_000]],
            ['name' => 'Ví điện tử', 'balance' => [100_000, 5_000_000]],
            ['name' => 'Tiết kiệm', 'balance' => [5_000_000, 80_000_000]],
        ];
        $wallet = fake()->randomElement($wallets);

        return [
            'user_id' => User::factory(),
            'name' => $wallet['name'].' '.fake()->unique()->numberBetween(10, 99999),
            'currency' => 'VND',
            'opening_balance' => fake()->randomFloat(2, $wallet['balance'][0], $wallet['balance'][1]),
            'is_default' => false,
        ];
    }

    public function defaultWallet(): static
    {
        return $this->state(fn (): array => [
            'is_default' => true,
        ]);
    }
}
