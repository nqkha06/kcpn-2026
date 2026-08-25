<?php

use Spatie\Permission\Models\Permission;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\patchJson;

test('an admin can update a permission', function () {
    $permission = Permission::firstOrCreate(['name' => 'manage users', 'guard_name' => 'web']);

    $response = actingAs(adminUser(), 'sanctum')->patchJson("/api/v1/admin/permissions/{$permission->id}", [
        'name' => 'manage staff',
    ]);

    $response->assertStatus(200);

    assertDatabaseHas('permissions', [
        'id' => $permission->id,
        'name' => 'manage staff',
    ]);
});

test('permission update validates a unique name', function () {
    $permission1 = Permission::firstOrCreate(['name' => 'manage users', 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => 'manage staff', 'guard_name' => 'web']);

    $response = actingAs(adminUser(), 'sanctum')->patchJson("/api/v1/admin/permissions/{$permission1->id}", [
        'name' => 'manage staff',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['name']);
});

test('a guest cannot update a permission', function () {
    $permission = Permission::firstOrCreate(['name' => 'guest update', 'guard_name' => 'web']);

    patchJson("/api/v1/admin/permissions/{$permission->id}", ['name' => 'changed'])
        ->assertUnauthorized();

    expect($permission->fresh()->name)->toBe('guest update');
});

test('a regular user cannot update a permission', function () {
    $permission = Permission::firstOrCreate(['name' => 'user update', 'guard_name' => 'web']);

    actingAs(regularUser())
        ->patchJson("/api/v1/admin/permissions/{$permission->id}", ['name' => 'changed'])
        ->assertForbidden();
});

test('updating a missing permission returns not found', function () {
    actingAs(adminUser(), 'sanctum')
        ->patchJson('/api/v1/admin/permissions/999999', ['name' => 'missing permission'])
        ->assertNotFound();
});
