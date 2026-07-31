<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            PermissionSeeder::class,
            AdminUserSeeder::class,
            CategorySeeder::class,
            PageSeeder::class,
            SettingSeeder::class,
            MenuSeeder::class,
            DemoDataSeeder::class,
            UserWalletSeeder::class,
        ]);
    }
}
