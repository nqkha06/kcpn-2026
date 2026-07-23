<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\UserWallet;
use Illuminate\Database\Seeder;

class UserWalletSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::query()->each(function (User $user): void {
            UserWallet::query()->firstOrCreate(
                [
                    'user_id' => $user->id,
                    'name' => 'Main Wallet',
                ],
                [
                    'currency' => 'VND',
                    'opening_balance' => 0,
                    'is_default' => true,
                ],
            );
        });
    }
}
