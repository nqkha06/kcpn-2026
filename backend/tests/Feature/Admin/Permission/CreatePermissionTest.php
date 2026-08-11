<?php

use Spatie\Permission\Models\Permission;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\getJson;

test('an admin can get permission options', function () {
    Permission::firstOrCreate(['name' => 'manage users', 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => 'manage roles', 'guard_name' => 'web']);

    $response = actingAs(adminUser(), 'sanctum')->getJson('/api/v1/admin/permissions/options');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                '*' => ['id', 'name'],
            ],
        ]);
});

test('a guest cannot get permission options', function () {
    getJson('/api/v1/admin/permissions/options')->assertUnauthorized();
});

test('a regular user cannot get permission options', function () {
    actingAs(regularUser())
        ->getJson('/api/v1/admin/permissions/options')
        ->assertForbidden();
});
