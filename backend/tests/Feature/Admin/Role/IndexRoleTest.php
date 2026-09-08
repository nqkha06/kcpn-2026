<?php

use Spatie\Permission\Models\Role;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\getJson;

test('an admin can list roles', function () {
    Role::firstOrCreate(['name' => 'editor', 'guard_name' => 'web']);

    $response = actingAs(adminUser(), 'sanctum')->getJson('/api/v1/admin/roles');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                '*' => ['id', 'name', 'created_at'],
            ],
        ]);
});

test('a guest cannot list roles', function () {
    getJson('/api/v1/admin/roles')->assertStatus(401);
});

test('a regular user cannot list roles', function () {
    actingAs(regularUser())
        ->getJson('/api/v1/admin/roles')
        ->assertForbidden();
});

test('an admin can search sort and paginate roles', function () {
    Role::firstOrCreate(['name' => 'zulu role', 'guard_name' => 'web']);
    $matching = Role::firstOrCreate(['name' => 'alpha role', 'guard_name' => 'web']);

    actingAs(adminUser(), 'sanctum')
        ->getJson('/api/v1/admin/roles?search=role&sort=name&direction=asc&per_page=1')
        ->assertOk()
        // Wait, there might be other seeded roles like 'admin' and 'user' or other roles.
        // So let's assert that the returned data has the correct format and sorting order.
        ->assertJsonPath('data.0.id', $matching->id);
});

test('role list query parameters are validated', function () {
    actingAs(adminUser(), 'sanctum')
        ->getJson('/api/v1/admin/roles?sort=guard_name&direction=sideways&per_page=101')
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['sort', 'direction', 'per_page']);
});

test('role list page query parameter applies BVA boundaries', function () {
    actingAs(adminUser(), 'sanctum')
        ->getJson('/api/v1/admin/roles?page=0')
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['page']);

    actingAs(adminUser(), 'sanctum')
        ->getJson('/api/v1/admin/roles?page=1')
        ->assertOk();
});
