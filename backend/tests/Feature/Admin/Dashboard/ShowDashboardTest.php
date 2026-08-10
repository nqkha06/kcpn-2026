<?php

use App\Models\Budget;
use App\Models\Category;
use App\Models\ExpenseTransaction;
use App\Models\UserWallet;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\getJson;

test('an admin can view dashboard aggregates', function () {
    $admin = adminUser();
    $user = regularUser();
    ExpenseTransaction::factory()->forUser($user)->income()->posted()->create();

    actingAs($admin)
        ->getJson('/api/v1/admin/dashboard')
        ->assertOk()
        ->assertJsonPath('data.stats.users', 2)
        ->assertJsonCount(6, 'data.monthlyFlow')
        ->assertJsonStructure(['data' => ['stats', 'monthlyFlow', 'topExpenseCategories', 'recentTransactions']]);
});

test('a guest cannot view the admin dashboard', function () {
    getJson('/api/v1/admin/dashboard')->assertUnauthorized();
});

test('a regular user cannot view the admin dashboard', function () {
    actingAs(regularUser())
        ->getJson('/api/v1/admin/dashboard')
        ->assertForbidden();
});

test('admin dashboard aggregates only transactions relevant to each metric', function () {
    $admin = adminUser();
    $user = regularUser();
    $wallet = UserWallet::factory()->for($user)->create();
    $category = Category::factory()->create(['status' => 'active']);
    Category::factory()->inactive()->create();
    Category::factory()->create(['user_id' => $user->id, 'status' => 'active']);
    Budget::factory()->for($user)->for($category)->monthly()->active()->create();
    Budget::factory()->for($user)->for($category)->create([
        'period' => 'yearly',
        'status' => 'inactive',
    ]);

    ExpenseTransaction::factory()->forUser($user)->income()->posted()->create([
        'wallet_id' => $wallet->id,
        'category_id' => $category->id,
        'amount' => 1000,
        'transacted_at' => now()->toDateString(),
    ]);
    ExpenseTransaction::factory()->forUser($user)->expense()->posted()->for($category)->create([
        'wallet_id' => $wallet->id,
        'amount' => 250,
        'transacted_at' => now()->toDateString(),
    ]);
    ExpenseTransaction::factory()->forUser($user)->expense()->pending()->create([
        'wallet_id' => $wallet->id,
        'category_id' => $category->id,
        'amount' => 999,
        'transacted_at' => now()->toDateString(),
    ]);
    ExpenseTransaction::factory()->forUser($user)->income()->posted()->create([
        'wallet_id' => $wallet->id,
        'category_id' => $category->id,
        'amount' => 5000,
        'transacted_at' => now()->subMonth()->toDateString(),
    ]);

    actingAs($admin)
        ->getJson('/api/v1/admin/dashboard')
        ->assertOk()
        ->assertJsonPath('data.stats.users', 2)
        ->assertJsonPath('data.stats.wallets', 1)
        ->assertJsonPath('data.stats.activeCategories', 1)
        ->assertJsonPath('data.stats.activeBudgets', 1)
        ->assertJsonPath('data.stats.postedIncomeThisMonth', 1000)
        ->assertJsonPath('data.stats.postedExpenseThisMonth', 250)
        ->assertJsonPath('data.stats.netThisMonth', 750)
        ->assertJsonPath('data.stats.pendingTransactions', 1)
        ->assertJsonPath('data.topExpenseCategories.0.id', $category->id)
        ->assertJsonPath('data.topExpenseCategories.0.amount', 250);
});
