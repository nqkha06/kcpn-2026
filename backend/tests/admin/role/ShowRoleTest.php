<?php

use Spatie\Permission\Models\Role;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;
use function Pest\Laravel\deleteJson;

test('an admin can delete a role', function () {
    $role = Role::create(['name' => 'manager', 'guard_name' => 'web']);

    $response = actingAs(adminUser(), 'sanctum')->deleteJson("/api/v1/admin/roles/{$role->id}");

    $response->assertStatus(200);

    assertDatabaseMissing('roles', [
        'id' => $role->id,
    ]);
});

test('an admin cannot delete the admin system role', function () {
    $admin = adminUser();
    $role = Role::where('name', 'admin')->first();

    $response = actingAs($admin, 'sanctum')->deleteJson("/api/v1/admin/roles/{$role->id}");

    $response->assertStatus(403);

    assertDatabaseHas('roles', [
        'id' => $role->id,
    ]);
});

test('an admin cannot delete the super admin system role', function () {
    $role = Role::create(['name' => 'super-admin', 'guard_name' => 'web']);

    actingAs(adminUser(), 'sanctum')
        ->deleteJson("/api/v1/admin/roles/{$role->id}")
        ->assertForbidden();

    assertDatabaseHas('roles', ['id' => $role->id]);
});

test('a guest cannot delete a role', function () {
    $role = Role::create(['name' => 'guest-delete', 'guard_name' => 'web']);

    deleteJson("/api/v1/admin/roles/{$role->id}")
        ->assertUnauthorized();

    assertDatabaseHas('roles', ['id' => $role->id]);
});

test('a regular user cannot delete a role', function () {
    $role = Role::create(['name' => 'user-delete', 'guard_name' => 'web']);

    actingAs(regularUser())
        ->deleteJson("/api/v1/admin/roles/{$role->id}")
        ->assertForbidden();
});

test('deleting a missing role returns not found', function () {
    actingAs(adminUser(), 'sanctum')
        ->deleteJson('/api/v1/admin/roles/999999')
        ->assertNotFound();
});
