<?php

use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;
use function Pest\Laravel\postJson;

test('an admin can create a role and assign permissions', function () {
    $permission = Permission::firstOrCreate(['name' => 'publish articles', 'guard_name' => 'web']);

    $response = actingAs(adminUser(), 'sanctum')->postJson('/api/v1/admin/roles', [
        'name' => 'writer',
        'permissions' => [$permission->id],
    ]);

    $response->assertStatus(201);

    assertDatabaseHas('roles', [
        'name' => 'writer',
        'guard_name' => 'web',
    ]);

    $role = Role::where('name', 'writer')->first();
    expect($role->hasPermissionTo('publish articles'))->toBeTrue();
});

test('a guest cannot create a role', function () {
    postJson('/api/v1/admin/roles', ['name' => 'guest-role'])
        ->assertUnauthorized();

    assertDatabaseMissing('roles', ['name' => 'guest-role']);
});

test('a regular user cannot create a role', function () {
    actingAs(regularUser())
        ->postJson('/api/v1/admin/roles', ['name' => 'unauthorized-role'])
        ->assertForbidden();

    assertDatabaseMissing('roles', ['name' => 'unauthorized-role']);
});

test('role creation validates the name and selected permissions', function () {
    $permission = Permission::firstOrCreate(['name' => 'manage reports', 'guard_name' => 'web']);

    actingAs(adminUser(), 'sanctum')
        ->postJson('/api/v1/admin/roles', [
            'name' => '',
            'permissions' => [$permission->id, $permission->id, 999999],
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['name', 'permissions.1', 'permissions.2']);
});

test('role creation rejects a duplicate web guard name', function () {
    Role::firstOrCreate(['name' => 'writer', 'guard_name' => 'web']);

    actingAs(adminUser(), 'sanctum')
        ->postJson('/api/v1/admin/roles', ['name' => 'writer'])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('name');
});
