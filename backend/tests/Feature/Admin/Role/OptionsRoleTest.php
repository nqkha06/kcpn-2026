<?php

use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\putJson;

test('an admin can update a role and sync permissions', function () {
    $role = Role::create(['name' => 'manager', 'guard_name' => 'web']);
    $permission1 = Permission::create(['name' => 'manage users', 'guard_name' => 'web']);
    $permission2 = Permission::create(['name' => 'manage roles', 'guard_name' => 'web']);

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
    $role = Role::create(['name' => 'guest-update', 'guard_name' => 'web']);

    putJson("/api/v1/admin/roles/{$role->id}", ['name' => 'changed'])
        ->assertUnauthorized();

    expect($role->fresh()->name)->toBe('guest-update');
});

test('a regular user cannot update a role', function () {
    $role = Role::create(['name' => 'user-update', 'guard_name' => 'web']);

    actingAs(regularUser())
        ->putJson("/api/v1/admin/roles/{$role->id}", ['name' => 'changed'])
        ->assertForbidden();
});

test('omitting permissions keeps the existing role permissions', function () {
    $role = Role::create(['name' => 'manager', 'guard_name' => 'web']);
    $permission = Permission::create(['name' => 'manage billing', 'guard_name' => 'web']);
    $role->givePermissionTo($permission);

    actingAs(adminUser(), 'sanctum')
        ->putJson("/api/v1/admin/roles/{$role->id}", ['name' => 'billing manager'])
        ->assertOk();

    expect($role->fresh()->hasPermissionTo($permission))->toBeTrue();
});

test('an empty permission list removes all role permissions', function () {
    $role = Role::create(['name' => 'manager', 'guard_name' => 'web']);
    $role->givePermissionTo(Permission::create(['name' => 'manage billing', 'guard_name' => 'web']));

    actingAs(adminUser(), 'sanctum')
        ->putJson("/api/v1/admin/roles/{$role->id}", [
            'name' => 'manager',
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
