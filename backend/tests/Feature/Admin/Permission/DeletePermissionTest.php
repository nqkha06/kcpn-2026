<?php

use Spatie\Permission\Models\Permission;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseMissing;
use function Pest\Laravel\deleteJson;

test('an admin can delete a permission', function () {
    $permission = Permission::firstOrCreate(['name' => 'delete-me', 'guard_name' => 'web']);

    $response = actingAs(adminUser(), 'sanctum')->deleteJson("/api/v1/admin/permissions/{$permission->id}");

    $response->assertStatus(200);

    assertDatabaseMissing('permissions', [
        'id' => $permission->id,
    ]);
});

test('a guest cannot delete a permission', function () {
    $permission = Permission::firstOrCreate(['name' => 'guest-delete-perm', 'guard_name' => 'web']);

    deleteJson("/api/v1/admin/permissions/{$permission->id}")
        ->assertUnauthorized();
});

test('a regular user cannot delete a permission', function () {
    $permission = Permission::firstOrCreate(['name' => 'user-delete-perm', 'guard_name' => 'web']);

    actingAs(regularUser())
        ->deleteJson("/api/v1/admin/permissions/{$permission->id}")
        ->assertForbidden();
});

test('deleting a missing permission returns not found', function () {
    actingAs(adminUser(), 'sanctum')
        ->deleteJson('/api/v1/admin/permissions/999999')
        ->assertNotFound();
});
