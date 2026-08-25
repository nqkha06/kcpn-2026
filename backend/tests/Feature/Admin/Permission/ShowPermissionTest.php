<?php

use Spatie\Permission\Models\Permission;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\getJson;

test('an admin can show a permission', function () {
    $permission = Permission::firstOrCreate(['name' => 'edit articles', 'guard_name' => 'web']);

    $response = actingAs(adminUser(), 'sanctum')->getJson("/api/v1/admin/permissions/{$permission->id}");

    $response->assertStatus(200)
        ->assertJson([
            'data' => [
                'id' => $permission->id,
                'name' => 'edit articles',
            ],
        ]);
});

test('a guest cannot show a permission', function () {
    $permission = Permission::firstOrCreate(['name' => 'guest view', 'guard_name' => 'web']);

    getJson("/api/v1/admin/permissions/{$permission->id}")
        ->assertUnauthorized();
});

test('a regular user cannot show a permission', function () {
    $permission = Permission::firstOrCreate(['name' => 'user view', 'guard_name' => 'web']);

    actingAs(regularUser())
        ->getJson("/api/v1/admin/permissions/{$permission->id}")
        ->assertForbidden();
});

test('showing a missing permission returns not found', function () {
    actingAs(adminUser(), 'sanctum')
        ->getJson('/api/v1/admin/permissions/999999')
        ->assertNotFound();
});
