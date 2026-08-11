<?php

use Spatie\Permission\Models\Permission;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\getJson;

test('an admin can list permissions', function () {
    Permission::firstOrCreate(['name' => 'edit articles', 'guard_name' => 'web']);

    $response = actingAs(adminUser(), 'sanctum')->getJson('/api/v1/admin/permissions');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                '*' => ['id', 'name', 'created_at'],
            ],
        ]);
});

test('a guest cannot list permissions', function () {
    $response = getJson('/api/v1/admin/permissions');
    $response->assertStatus(401);
});

test('a regular user cannot list permissions', function () {
    actingAs(regularUser())
        ->getJson('/api/v1/admin/permissions')
        ->assertForbidden();
});

test('an admin can search sort and paginate permissions', function () {
    Permission::create(['name' => 'zulu report', 'guard_name' => 'web']);
    $matching = Permission::create(['name' => 'alpha report', 'guard_name' => 'web']);

    actingAs(adminUser(), 'sanctum')
        ->getJson('/api/v1/admin/permissions?search=report&sort=name&direction=asc&per_page=1')
        ->assertOk()
        ->assertJsonPath('meta.total', 2)
        ->assertJsonPath('data.0.id', $matching->id);
});

test('permission list query parameters are validated', function () {
    actingAs(adminUser(), 'sanctum')
        ->getJson('/api/v1/admin/permissions?sort=guard_name&direction=sideways&per_page=101')
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['sort', 'direction', 'per_page']);
});
