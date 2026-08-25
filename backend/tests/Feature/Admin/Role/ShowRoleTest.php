<?php

use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\getJson;

test('an admin can show a role and its permissions', function () {
    $role = Role::firstOrCreate(['name' => 'editor', 'guard_name' => 'web']);
    $permission = Permission::firstOrCreate(['name' => 'edit articles', 'guard_name' => 'web']);
    $role->givePermissionTo($permission);

    $response = actingAs(adminUser(), 'sanctum')->getJson("/api/v1/admin/roles/{$role->id}");

    $response->assertStatus(200)
        ->assertJson([
            'data' => [
                'id' => $role->id,
                'name' => 'editor',
                'permissions' => [
                    [
                        'id' => $permission->id,
                        'name' => 'edit articles',
                    ],
                ],
            ],
        ]);
});

test('a guest cannot show a role', function () {
    $role = Role::firstOrCreate(['name' => 'guest-view', 'guard_name' => 'web']);

    getJson("/api/v1/admin/roles/{$role->id}")
        ->assertUnauthorized();
});

test('a regular user cannot show a role', function () {
    $role = Role::firstOrCreate(['name' => 'user-view', 'guard_name' => 'web']);

    actingAs(regularUser())
        ->getJson("/api/v1/admin/roles/{$role->id}")
        ->assertForbidden();
});

test('showing a missing role returns not found', function () {
    actingAs(adminUser(), 'sanctum')
        ->getJson('/api/v1/admin/roles/999999')
        ->assertNotFound();
});
