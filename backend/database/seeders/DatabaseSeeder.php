<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        $this->call(PermissionSeeder::class);

        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
        $user->assignRole(Role::findOrCreate('user', 'web'));

        $this->call(CategorySeeder::class);
        $this->call(UserWalletSeeder::class);
        $this->call(MenuSeeder::class);
        $this->call(DemoDataSeeder::class);
    }
}
