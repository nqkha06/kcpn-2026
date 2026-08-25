<?php

use Spatie\Permission\Models\Permission;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;
use function Pest\Laravel\postJson;

test('an admin can create a permission', function () {
    $response = actingAs(adminUser(), 'sanctum')->postJson('/api/v1/admin/permissions', [
        'name' => 'publish articles',
    ]);

    $response->assertStatus(201);

    assertDatabaseHas('permissions', [
        'name' => 'publish articles',
        'guard_name' => 'web',
    ]);
});

test('permission creation validates a unique name', function () {
    Permission::firstOrCreate(['name' => 'publish articles', 'guard_name' => 'web']);

    $response = actingAs(adminUser(), 'sanctum')->postJson('/api/v1/admin/permissions', [
        'name' => 'publish articles',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['name']);
});

test('a guest cannot create a permission', function () {
    postJson('/api/v1/admin/permissions', ['name' => 'guest permission'])
        ->assertUnauthorized();

    assertDatabaseMissing('permissions', ['name' => 'guest permission']);
});

test('a regular user cannot create a permission', function () {
    actingAs(regularUser())
        ->postJson('/api/v1/admin/permissions', ['name' => 'user permission'])
        ->assertForbidden();
});

test('permission creation requires a name', function () {
    actingAs(adminUser(), 'sanctum')
        ->postJson('/api/v1/admin/permissions', [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('name');
});
