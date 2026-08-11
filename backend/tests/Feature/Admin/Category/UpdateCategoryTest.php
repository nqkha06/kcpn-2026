<?php

use App\Models\Category;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\patchJson;

test('an admin can update a category', function () {
    $category = Category::factory()->create();

    actingAs(adminUser())
        ->patchJson("/api/v1/admin/categories/{$category->id}", [
            'name' => 'Updated Category',
            'color' => '#3B82F6',
            'description' => 'Updated',
            'status' => 'inactive',
        ])
        ->assertOk()
        ->assertJsonPath('data.status', 'inactive');

    assertDatabaseHas('categories', ['id' => $category->id, 'name' => 'Updated Category']);
});

test('a guest cannot update an admin category', function () {
    $category = Category::factory()->create();

    patchJson("/api/v1/admin/categories/{$category->id}", [
        'name' => 'Guest Update',
        'color' => '#3B82F6',
        'status' => 'active',
    ])->assertUnauthorized();
});

test('a regular user cannot update an admin category', function () {
    $category = Category::factory()->create();

    actingAs(regularUser())
        ->patchJson("/api/v1/admin/categories/{$category->id}", [
            'name' => 'Unauthorized Update',
            'color' => '#3B82F6',
            'status' => 'active',
        ])
        ->assertForbidden();
});

test('admin category update validates required fields', function () {
    $category = Category::factory()->create();

    actingAs(adminUser())
        ->patchJson("/api/v1/admin/categories/{$category->id}", [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['name', 'color', 'status']);
});

test('admin category update rejects a duplicate global name', function () {
    Category::factory()->create(['name' => 'Food']);
    $category = Category::factory()->create(['name' => 'Travel']);

    actingAs(adminUser())
        ->patchJson("/api/v1/admin/categories/{$category->id}", [
            'name' => 'Food',
            'color' => '#3B82F6',
            'status' => 'active',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('name');

    expect($category->fresh()->name)->toBe('Travel');
});

test('updating a missing admin category returns not found', function () {
    actingAs(adminUser())
        ->patchJson('/api/v1/admin/categories/999999', [
            'name' => 'Missing Category',
            'color' => '#3B82F6',
            'status' => 'active',
        ])
        ->assertNotFound();
});
