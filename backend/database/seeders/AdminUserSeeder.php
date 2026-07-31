<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::query()->firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'System Administrator',
                'first_name' => 'Administrator',
                'last_name' => 'System',
                'phone_number' => '0900000000',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
            ],
        );

        $admin->forceFill([
            'name' => 'System Administrator',
            'first_name' => 'Administrator',
            'last_name' => 'System',
            'email_verified_at' => $admin->email_verified_at ?? now(),
        ])->save();

        $admin->assignRole(Role::findOrCreate('admin', 'web'));

        foreach ([
            'currency' => 'VND',
            'locale' => 'vi',
            'timezone' => 'Asia/Ho_Chi_Minh',
        ] as $key => $value) {
            $admin->setMeta($key, $value);
        }
    }
}
