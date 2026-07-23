<?php

namespace Database\Seeders;

use App\Support\Authorization\PermissionCatalog;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (PermissionCatalog::all() as $permissionName) {
            Permission::findOrCreate($permissionName, PermissionCatalog::GUARD);
        }

        foreach ([PermissionCatalog::ADMIN_ROLE, PermissionCatalog::USER_ROLE] as $roleName) {
            $role = Role::findOrCreate($roleName, PermissionCatalog::GUARD);
            $role->givePermissionTo(PermissionCatalog::forRole($roleName));
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
