<?php

use App\Models\Budget;
use App\Models\Category;
use App\Models\ExpenseTransaction;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;
use function Pest\Laravel\deleteJson;

test('an admin can delete a category', function () {
    $category = Category::factory()->create();

    actingAs(adminUser())
        ->deleteJson("/api/v1/admin/categories/{$category->id}")
        ->assertOk();

    assertDatabaseMissing('categories', ['id' => $category->id]);
});

test('a guest cannot delete an admin category', function () {
    $category = Category::factory()->create();

    deleteJson("/api/v1/admin/categories/{$category->id}")
        ->assertUnauthorized();

    assertDatabaseHas('categories', ['id' => $category->id]);
});

test('a regular user cannot delete an admin category', function () {
    $category = Category::factory()->create();

    actingAs(regularUser())
        ->deleteJson("/api/v1/admin/categories/{$category->id}")
        ->assertForbidden();
});

test('deleting a global category removes budgets and clears transaction categories', function () {
    $user = regularUser();
    $category = Category::factory()->create();
    $budget = Budget::factory()->for($user)->create(['category_id' => $category->id]);
    $transaction = ExpenseTransaction::factory()->forUser($user)->create([
        'category_id' => $category->id,
    ]);

    actingAs(adminUser())
        ->deleteJson("/api/v1/admin/categories/{$category->id}")
        ->assertOk();

    assertDatabaseMissing('budgets', ['id' => $budget->id]);
    assertDatabaseHas('expense_transactions', [
        'id' => $transaction->id,
        'category_id' => null,
    ]);
});

test('deleting a missing admin category returns not found', function () {
    actingAs(adminUser())
        ->deleteJson('/api/v1/admin/categories/999999')
        ->assertNotFound();
});
