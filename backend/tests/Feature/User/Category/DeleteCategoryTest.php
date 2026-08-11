<?php

use App\Models\Budget;
use App\Models\Category;
use App\Models\ExpenseTransaction;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;
use function Pest\Laravel\deleteJson;

test('a user can delete their unused private category', function () {
    $user = regularUser();
    $category = Category::factory()->create(['user_id' => $user->id]);

    actingAs($user)
        ->deleteJson("/api/v1/user/categories/{$category->id}")
        ->assertOk();

    assertDatabaseMissing('categories', ['id' => $category->id]);
});

test('a guest cannot delete a private category', function () {
    $category = Category::factory()->create(['user_id' => regularUser()->id]);

    deleteJson("/api/v1/user/categories/{$category->id}")
        ->assertUnauthorized();

    assertDatabaseHas('categories', ['id' => $category->id]);
});

test('a user cannot delete another users private category', function () {
    $category = Category::factory()->create(['user_id' => regularUser()->id]);

    actingAs(regularUser())
        ->deleteJson("/api/v1/user/categories/{$category->id}")
        ->assertForbidden();

    assertDatabaseHas('categories', ['id' => $category->id]);
});

test('a user cannot delete a global category', function () {
    $category = Category::factory()->create();

    actingAs(regularUser())
        ->deleteJson("/api/v1/user/categories/{$category->id}")
        ->assertForbidden();

    assertDatabaseHas('categories', ['id' => $category->id]);
});

test('a category used by a transaction cannot be deleted', function () {
    $user = regularUser();
    $category = Category::factory()->create(['user_id' => $user->id]);
    ExpenseTransaction::factory()->forUser($user)->create(['category_id' => $category->id]);

    actingAs($user)
        ->deleteJson("/api/v1/user/categories/{$category->id}")
        ->assertStatus(409)
        ->assertJsonValidationErrors('category');

    assertDatabaseHas('categories', ['id' => $category->id]);
});

test('a category used by a budget cannot be deleted', function () {
    $user = regularUser();
    $category = Category::factory()->create(['user_id' => $user->id]);
    Budget::factory()->for($user)->create(['category_id' => $category->id]);

    actingAs($user)
        ->deleteJson("/api/v1/user/categories/{$category->id}")
        ->assertStatus(409)
        ->assertJsonValidationErrors('category');

    assertDatabaseHas('categories', ['id' => $category->id]);
});

test('deleting a missing category returns not found', function () {
    actingAs(regularUser())
        ->deleteJson('/api/v1/user/categories/999999')
        ->assertNotFound();
});
