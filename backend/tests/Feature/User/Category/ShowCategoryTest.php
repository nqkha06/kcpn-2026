<?php

use App\Models\Category;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\getJson;

test('a user can view their private category', function () {
    $user = regularUser();
    $category = Category::factory()->create(['user_id' => $user->id]);

    actingAs($user)
        ->getJson("/api/v1/user/categories/{$category->id}")
        ->assertOk()
        ->assertJsonPath('data.id', $category->id);
});

test('a user cannot view another users private category', function () {
    $owner = regularUser();
    $category = Category::factory()->create(['user_id' => $owner->id]);

    actingAs(regularUser())
        ->getJson("/api/v1/user/categories/{$category->id}")
        ->assertForbidden();
});

test('a user can view a global category', function () {
    $category = Category::factory()->create();

    actingAs(regularUser())
        ->getJson("/api/v1/user/categories/{$category->id}")
        ->assertOk()
        ->assertJsonPath('data.id', $category->id)
        ->assertJsonPath('data.is_private', false);
});

test('a guest cannot view a category', function () {
    $category = Category::factory()->create();

    getJson("/api/v1/user/categories/{$category->id}")
        ->assertUnauthorized();
});

test('viewing a missing category returns not found', function () {
    actingAs(regularUser())
        ->getJson('/api/v1/user/categories/999999')
        ->assertNotFound();
});
