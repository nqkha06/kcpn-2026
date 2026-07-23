<?php

use App\Support\Authorization\PermissionCatalog;
use Database\Seeders\PermissionSeeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

test('permission catalog contains every supported module without duplicate names', function () {
    $permissions = PermissionCatalog::all();
    $modules = PermissionCatalog::modules();

    expect($modules)->toHaveCount(17)
        ->and($permissions)->toHaveCount(54)
        ->and(array_unique($permissions))->toHaveCount(54)
        ->and($permissions)->toContain(
            'admin.dashboard.view',
            'admin.permissions.delete',
            'admin.languages.update',
            'user.dashboard.view',
            'user.categories.delete',
            'user.settings.update',
        );
});

test('permission seeder creates the full catalog and default role grants', function () {
    $this->seed(PermissionSeeder::class);

    $adminRole = Role::findByName(PermissionCatalog::ADMIN_ROLE, PermissionCatalog::GUARD);
    $userRole = Role::findByName(PermissionCatalog::USER_ROLE, PermissionCatalog::GUARD);

    expect(Permission::query()->where('guard_name', PermissionCatalog::GUARD)->count())
        ->toBe(count(PermissionCatalog::all()))
        ->and($adminRole->permissions()->pluck('name')->all())
        ->toEqualCanonicalizing(PermissionCatalog::forRole(PermissionCatalog::ADMIN_ROLE))
        ->and($userRole->permissions()->pluck('name')->all())
        ->toEqualCanonicalizing(PermissionCatalog::forRole(PermissionCatalog::USER_ROLE))
        ->and($userRole->hasPermissionTo('admin.dashboard.view'))->toBeFalse();
});

test('permission seeder is idempotent and preserves custom grants', function () {
    $customPermission = Permission::findOrCreate('custom.reports.export', PermissionCatalog::GUARD);
    $adminRole = Role::findOrCreate(PermissionCatalog::ADMIN_ROLE, PermissionCatalog::GUARD);
    $userRole = Role::findOrCreate(PermissionCatalog::USER_ROLE, PermissionCatalog::GUARD);
    $adminRole->givePermissionTo($customPermission);
    $userRole->givePermissionTo($customPermission);

    $this->seed(PermissionSeeder::class);
    $this->seed(PermissionSeeder::class);

    $adminRole->refresh();
    $userRole->refresh();

    expect(Permission::query()->where('guard_name', PermissionCatalog::GUARD)->count())
        ->toBe(count(PermissionCatalog::all()) + 1)
        ->and($adminRole->hasPermissionTo($customPermission))->toBeTrue()
        ->and($userRole->hasPermissionTo($customPermission))->toBeTrue()
        ->and(Permission::query()->where('name', 'admin.users.view')->count())->toBe(1);
});
