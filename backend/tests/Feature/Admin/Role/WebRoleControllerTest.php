<?php

use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;

test('web admin can view roles list with search and pagination', function () {
    $admin = adminUser();
    $role1 = Role::firstOrCreate(['name' => 'editor-role', 'guard_name' => 'web']);
    $role2 = Role::firstOrCreate(['name' => 'viewer-role', 'guard_name' => 'web']);

    actingAs($admin, 'web')
        ->get(route('admin.roles.index', ['search' => 'editor']))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Admin/roles/list')
            ->has('roles')
            ->has('pagination')
            ->where('filters.search', 'editor')
        );
});

test('web admin can view create role form', function () {
    actingAs(adminUser(), 'web')
        ->get(route('admin.roles.create'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Admin/roles/add')
            ->has('permissions')
        );
});

test('web admin can store a new role with permissions', function () {
    $perm = Permission::firstOrCreate(['name' => 'manage-reports', 'guard_name' => 'web']);

    actingAs(adminUser(), 'web')
        ->post(route('admin.roles.store'), [
            'name' => 'finance-manager',
            'permissions' => [$perm->name],
        ])
        ->assertRedirect(route('admin.roles.index'))
        ->assertSessionHas('success');

    assertDatabaseHas('roles', ['name' => 'finance-manager']);
});

test('web admin store validates unique role name', function () {
    Role::firstOrCreate(['name' => 'duplicate-role', 'guard_name' => 'web']);

    actingAs(adminUser(), 'web')
        ->post(route('admin.roles.store'), [
            'name' => 'duplicate-role',
        ])
        ->assertSessionHasErrors('name');
});

test('web admin can view edit role form', function () {
    $role = Role::firstOrCreate(['name' => 'edit-target-role', 'guard_name' => 'web']);

    actingAs(adminUser(), 'web')
        ->get(route('admin.roles.edit', $role))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Admin/roles/edit')
            ->where('role.name', 'edit-target-role')
            ->has('permissions')
        );
});

test('web admin can update role and permissions', function () {
    $role = Role::firstOrCreate(['name' => 'update-target-role', 'guard_name' => 'web']);
    $perm = Permission::firstOrCreate(['name' => 'edit-settings', 'guard_name' => 'web']);

    actingAs(adminUser(), 'web')
        ->put(route('admin.roles.update', $role), [
            'name' => 'updated-role-name',
            'permissions' => [$perm->name],
        ])
        ->assertRedirect(route('admin.roles.index'))
        ->assertSessionHas('success');

    assertDatabaseHas('roles', ['name' => 'updated-role-name']);
});

test('web admin can delete a role', function () {
    $role = Role::firstOrCreate(['name' => 'delete-target-role', 'guard_name' => 'web']);

    actingAs(adminUser(), 'web')
        ->delete(route('admin.roles.destroy', $role))
        ->assertRedirect(route('admin.roles.index'))
        ->assertSessionHas('success');

    assertDatabaseMissing('roles', ['id' => $role->id]);
});
