<?php

use Spatie\Permission\Models\Role;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\getJson;

test('an admin can get role options', function () {
    Role::create(['name' => 'manager', 'guard_name' => 'web']);
    Role::create(['name' => 'editor', 'guard_name' => 'web']);

    $response = actingAs(adminUser(), 'sanctum')->getJson('/api/v1/admin/roles/options');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                '*' => ['id', 'name'],
            ],
        ]);
});

test('a guest cannot get role options', function () {
    getJson('/api/v1/admin/roles/options')->assertUnauthorized();
});

test('a regular user cannot get role options', function () {
    actingAs(regularUser())
        ->getJson('/api/v1/admin/roles/options')
        ->assertForbidden();
});
