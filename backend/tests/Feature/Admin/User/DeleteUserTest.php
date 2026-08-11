<?php

use App\Models\Budget;
use App\Models\Category;
use App\Models\ExpenseTransaction;
use App\Models\User;
use App\Models\UserWallet;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;
use function Pest\Laravel\deleteJson;

test('an admin can delete a user', function () {
    $user = User::factory()->create();

    actingAs(adminUser())
        ->deleteJson("/api/v1/admin/users/{$user->id}")
        ->assertOk();

    assertDatabaseMissing('users', ['id' => $user->id]);
});

test('a guest cannot delete a user', function () {
    $user = User::factory()->create();

    deleteJson("/api/v1/admin/users/{$user->id}")
        ->assertUnauthorized();

    assertDatabaseHas('users', ['id' => $user->id]);
});

test('a regular user cannot delete a user', function () {
    $user = User::factory()->create();

    actingAs(regularUser())
        ->deleteJson("/api/v1/admin/users/{$user->id}")
        ->assertForbidden();

    assertDatabaseHas('users', ['id' => $user->id]);
});

test('deleting a user removes their finance data and preferences', function () {
    $user = regularUser();
    $wallet = UserWallet::factory()->for($user)->create();
    $category = Category::factory()->create(['user_id' => $user->id]);
    $transaction = ExpenseTransaction::factory()->forUser($user)->create([
        'wallet_id' => $wallet->id,
        'category_id' => $category->id,
    ]);
    $budget = Budget::factory()->for($user)->create(['category_id' => $category->id]);
    $user->setMeta('currency', 'USD');

    actingAs(adminUser())
        ->deleteJson("/api/v1/admin/users/{$user->id}")
        ->assertOk();

    assertDatabaseMissing('users', ['id' => $user->id]);
    assertDatabaseMissing('user_wallets', ['id' => $wallet->id]);
    assertDatabaseMissing('categories', ['id' => $category->id]);
    assertDatabaseMissing('expense_transactions', ['id' => $transaction->id]);
    assertDatabaseMissing('budgets', ['id' => $budget->id]);
    assertDatabaseMissing('user_metas', ['user_id' => $user->id]);
});

test('deleting a missing user returns not found', function () {
    actingAs(adminUser())
        ->deleteJson('/api/v1/admin/users/999999')
        ->assertNotFound();
});
