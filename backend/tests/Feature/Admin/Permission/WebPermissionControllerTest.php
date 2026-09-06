<?php

use Spatie\Permission\Models\Permission;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;

test('web admin can view permissions list with search and pagination', function () {
    $admin = adminUser();
    Permission::firstOrCreate(['name' => 'view-posts', 'guard_name' => 'web']);

    actingAs($admin, 'web')
        ->get(route('admin.permissions.index', ['search' => 'posts']))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Admin/permissions/list')
            ->has('permissions')
            ->has('pagination')
            ->where('filters.search', 'posts')
        );
});

test('web admin can view create permission form', function () {
    actingAs(adminUser(), 'web')
        ->get(route('admin.permissions.create'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Admin/permissions/add')
        );
});

test('web admin can store a new permission', function () {
    actingAs(adminUser(), 'web')
        ->post(route('admin.permissions.store'), [
            'name' => 'create-invoices',
        ])
        ->assertRedirect(route('admin.permissions.index'))
        ->assertSessionHas('success');

    assertDatabaseHas('permissions', ['name' => 'create-invoices']);
});

test('web admin store validates unique permission name', function () {
    Permission::firstOrCreate(['name' => 'existing-perm', 'guard_name' => 'web']);

    actingAs(adminUser(), 'web')
        ->post(route('admin.permissions.store'), [
            'name' => 'existing-perm',
        ])
        ->assertSessionHasErrors('name');
});

test('web admin can view edit permission form', function () {
    $perm = Permission::firstOrCreate(['name' => 'edit-target-perm', 'guard_name' => 'web']);

    actingAs(adminUser(), 'web')
        ->get(route('admin.permissions.edit', $perm))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Admin/permissions/edit')
            ->where('permission.name', 'edit-target-perm')
        );
});

test('web admin can update a permission', function () {
    $perm = Permission::firstOrCreate(['name' => 'update-target-perm', 'guard_name' => 'web']);

    actingAs(adminUser(), 'web')
        ->put(route('admin.permissions.update', $perm), [
            'name' => 'updated-perm-name',
        ])
        ->assertRedirect(route('admin.permissions.index'))
        ->assertSessionHas('success');

    assertDatabaseHas('permissions', ['name' => 'updated-perm-name']);
});

test('web admin can delete a permission', function () {
    $perm = Permission::firstOrCreate(['name' => 'delete-target-perm', 'guard_name' => 'web']);

    actingAs(adminUser(), 'web')
        ->delete(route('admin.permissions.destroy', $perm))
        ->assertRedirect(route('admin.permissions.index'))
        ->assertSessionHas('success');

    assertDatabaseMissing('permissions', ['id' => $perm->id]);
});
