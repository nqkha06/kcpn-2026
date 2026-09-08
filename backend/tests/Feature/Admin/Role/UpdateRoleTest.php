<?php

use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\putJson;

test('an admin can update a role and sync permissions', function () {
    $role = Role::firstOrCreate(['name' => 'manager', 'guard_name' => 'web']);
    $permission1 = Permission::firstOrCreate(['name' => 'manage users', 'guard_name' => 'web']);
    $permission2 = Permission::firstOrCreate(['name' => 'manage roles', 'guard_name' => 'web']);

    $role->givePermissionTo($permission1);

    $response = actingAs(adminUser(), 'sanctum')->putJson("/api/v1/admin/roles/{$role->id}", [
        'name' => 'senior manager',
        'permissions' => [$permission2->id],
    ]);

    $response->assertStatus(200);

    assertDatabaseHas('roles', [
        'id' => $role->id,
        'name' => 'senior manager',
    ]);

    expect($role->fresh()->hasPermissionTo($permission2))->toBeTrue();
    expect($role->fresh()->hasPermissionTo($permission1))->toBeFalse();
});

test('a guest cannot update a role', function () {
    $role = Role::firstOrCreate(['name' => 'guest-update', 'guard_name' => 'web']);

    putJson("/api/v1/admin/roles/{$role->id}", ['name' => 'changed'])
        ->assertUnauthorized();

    expect($role->fresh()->name)->toBe('guest-update');
});

test('a regular user cannot update a role', function () {
    $role = Role::firstOrCreate(['name' => 'user-update', 'guard_name' => 'web']);

    actingAs(regularUser())
        ->putJson("/api/v1/admin/roles/{$role->id}", ['name' => 'changed'])
        ->assertForbidden();
});

test('omitting permissions keeps the existing role permissions', function () {
    $role = Role::firstOrCreate(['name' => 'manager-omit', 'guard_name' => 'web']);
    $permission = Permission::firstOrCreate(['name' => 'manage billing', 'guard_name' => 'web']);
    $role->givePermissionTo($permission);

    actingAs(adminUser(), 'sanctum')
        ->putJson("/api/v1/admin/roles/{$role->id}", ['name' => 'billing manager'])
        ->assertOk();

    expect($role->fresh()->hasPermissionTo($permission))->toBeTrue();
});

test('an empty permission list removes all role permissions', function () {
    $role = Role::firstOrCreate(['name' => 'manager-empty', 'guard_name' => 'web']);
    $permission = Permission::firstOrCreate(['name' => 'manage billing', 'guard_name' => 'web']);
    $role->givePermissionTo($permission);

    actingAs(adminUser(), 'sanctum')
        ->putJson("/api/v1/admin/roles/{$role->id}", [
            'name' => 'manager-empty',
            'permissions' => [],
        ])
        ->assertOk()
        ->assertJsonCount(0, 'data.permissions');

    expect($role->fresh()->permissions)->toBeEmpty();
});

test('updating a missing role returns not found', function () {
    actingAs(adminUser(), 'sanctum')
        ->putJson('/api/v1/admin/roles/999999', ['name' => 'missing-role'])
        ->assertNotFound();
});

test('role update validates permissions must be an array with integer elements', function () {
    $role = Role::firstOrCreate(['name' => 'role-update-invalid-perm', 'guard_name' => 'web']);

    actingAs(adminUser(), 'sanctum')
        ->putJson("/api/v1/admin/roles/{$role->id}", [
            'name' => 'role-update-invalid-perm',
            'permissions' => 'not-an-array',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['permissions']);

    actingAs(adminUser(), 'sanctum')
        ->putJson("/api/v1/admin/roles/{$role->id}", [
            'name' => 'role-update-invalid-perm',
            'permissions' => ['not-an-integer'],
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['permissions.0']);
});
