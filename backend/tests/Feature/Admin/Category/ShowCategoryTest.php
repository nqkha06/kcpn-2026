<?php

use App\Models\Category;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\getJson;

test('an admin can view a category', function () {
    $category = Category::factory()->create();

    actingAs(adminUser())
        ->getJson("/api/v1/admin/categories/{$category->id}")
        ->assertOk()
        ->assertJsonPath('data.id', $category->id);
});

test('a guest cannot view an admin category', function () {
    $category = Category::factory()->create();

    getJson("/api/v1/admin/categories/{$category->id}")
        ->assertUnauthorized();
});

test('a regular user cannot view an admin category', function () {
    $category = Category::factory()->create();

    actingAs(regularUser())
        ->getJson("/api/v1/admin/categories/{$category->id}")
        ->assertForbidden();
});

test('an admin cannot view another users private category through the admin endpoint', function () {
    $category = Category::factory()->create(['user_id' => regularUser()->id]);

    actingAs(adminUser())
        ->getJson("/api/v1/admin/categories/{$category->id}")
        ->assertForbidden();
});

test('viewing a missing admin category returns not found', function () {
    actingAs(adminUser())
        ->getJson('/api/v1/admin/categories/999999')
        ->assertNotFound();
});
