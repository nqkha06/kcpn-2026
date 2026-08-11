<?php

use App\Models\Category;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;
use function Pest\Laravel\postJson;

test('an admin can create a category', function () {
    actingAs(adminUser())
        ->postJson('/api/v1/admin/categories', [
            'name' => 'API Category',
            'color' => '#10B981',
            'description' => 'Created through API tests',
            'status' => 'active',
        ])
        ->assertCreated()
        ->assertJsonPath('data.name', 'API Category');

    assertDatabaseHas('categories', ['name' => 'API Category', 'user_id' => null]);
});

test('a guest cannot create an admin category', function () {
    postJson('/api/v1/admin/categories', [
        'name' => 'Guest Category',
        'color' => '#10B981',
        'status' => 'active',
    ])->assertUnauthorized();

    assertDatabaseMissing('categories', ['name' => 'Guest Category']);
});

test('a regular user cannot create an admin category', function () {
    actingAs(regularUser())
        ->postJson('/api/v1/admin/categories', [
            'name' => 'Unauthorized Category',
            'color' => '#10B981',
            'status' => 'active',
        ])
        ->assertForbidden();
});

test('admin category creation validates required fields', function () {
    actingAs(adminUser())
        ->postJson('/api/v1/admin/categories', [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['name', 'color', 'status']);
});

test('admin category creation validates color and status', function () {
    actingAs(adminUser())
        ->postJson('/api/v1/admin/categories', [
            'name' => 'Invalid Category',
            'color' => 'green',
            'status' => 'archived',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['color', 'status']);
});

test('admin category creation rejects a duplicate global name', function () {
    Category::factory()->create(['name' => 'Food']);

    actingAs(adminUser())
        ->postJson('/api/v1/admin/categories', [
            'name' => 'Food',
            'color' => '#10B981',
            'status' => 'active',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('name');
});

test('an admin can create a global category with the same name as a private category', function () {
    Category::factory()->create([
        'user_id' => regularUser()->id,
        'name' => 'Private Name',
    ]);

    actingAs(adminUser())
        ->postJson('/api/v1/admin/categories', [
            'name' => 'Private Name',
            'color' => '#10B981',
            'status' => 'active',
        ])
        ->assertCreated();

    assertDatabaseHas('categories', [
        'user_id' => null,
        'name' => 'Private Name',
    ]);
});
