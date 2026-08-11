<?php

use App\Models\Category;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;
use function Pest\Laravel\patchJson;

test('a user can update their private category', function () {
    $user = regularUser();
    $category = Category::factory()->create(['user_id' => $user->id]);

    actingAs($user)
        ->patchJson("/api/v1/user/categories/{$category->id}", [
            'name' => 'Updated Private Category',
            'color' => '#3B82F6',
            'description' => 'Updated',
        ])
        ->assertOk()
        ->assertJsonPath('data.name', 'Updated Private Category');

    assertDatabaseHas('categories', ['id' => $category->id, 'name' => 'Updated Private Category']);
});

test('a guest cannot update a private category', function () {
    $category = Category::factory()->create(['user_id' => regularUser()->id]);

    patchJson("/api/v1/user/categories/{$category->id}", [
        'name' => 'Guest Update',
        'color' => '#3B82F6',
    ])->assertUnauthorized();

    assertDatabaseMissing('categories', [
        'id' => $category->id,
        'name' => 'Guest Update',
    ]);
});

test('a user cannot update another users private category', function () {
    $category = Category::factory()->create(['user_id' => regularUser()->id]);

    actingAs(regularUser())
        ->patchJson("/api/v1/user/categories/{$category->id}", [
            'name' => 'Stolen Category',
            'color' => '#3B82F6',
        ])
        ->assertForbidden();

    assertDatabaseMissing('categories', [
        'id' => $category->id,
        'name' => 'Stolen Category',
    ]);
});

test('a user cannot update a global category', function () {
    $category = Category::factory()->create();

    actingAs(regularUser())
        ->patchJson("/api/v1/user/categories/{$category->id}", [
            'name' => 'Changed Global',
            'color' => '#3B82F6',
        ])
        ->assertForbidden();
});

test('category update validates the name and color', function () {
    $user = regularUser();
    $category = Category::factory()->create(['user_id' => $user->id]);

    actingAs($user)
        ->patchJson("/api/v1/user/categories/{$category->id}", [
            'name' => '',
            'color' => 'blue',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['name', 'color']);
});

test('a private category cannot be renamed to a visible category name', function () {
    $user = regularUser();
    Category::factory()->create(['name' => 'Food']);
    $category = Category::factory()->create([
        'user_id' => $user->id,
        'name' => 'Lunch',
    ]);

    actingAs($user)
        ->patchJson("/api/v1/user/categories/{$category->id}", [
            'name' => 'Food',
            'color' => '#3B82F6',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('name');

    expect($category->fresh()->name)->toBe('Lunch');
});

test('updating a missing category returns not found', function () {
    actingAs(regularUser())
        ->patchJson('/api/v1/user/categories/999999', [
            'name' => 'Missing Category',
            'color' => '#3B82F6',
        ])
        ->assertNotFound();
});
