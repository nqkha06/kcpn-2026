<?php

use App\Models\Budget;
use App\Models\Category;
use App\Models\ExpenseTransaction;
use App\Models\User;
use App\Models\UserWallet;
use Illuminate\Support\Carbon;
use Spatie\Permission\Models\Role;

beforeEach(function (): void {
    Carbon::setTestNow('2026-07-22 10:00:00');

    $this->withHeaders([
        'Accept' => 'application/json',
        'Origin' => 'http://localhost:3000',
        'Referer' => 'http://localhost:3000/user/budgets',
    ]);
});

afterEach(function (): void {
    Carbon::setTestNow();
});

test('guests cannot list budgets', function () {
    $this->getJson('/api/v1/user/budgets')
        ->assertUnauthorized()
        ->assertJsonPath('message', 'Unauthenticated');
});

test('user can list only their own active budgets', function () {
    $user = User::factory()->create();
    $user->assignRole(Role::findOrCreate('user', 'web'));
    $otherUser = User::factory()->create();
    $otherUser->assignRole(Role::findOrCreate('user', 'web'));

    $category = Category::factory()->create();
    $inactiveCategory = Category::factory()->create();
    $activeBudget = Budget::factory()->for($user)->for($category)->active()->create();
    Budget::factory()->for($user)->for($inactiveCategory)->create(['status' => 'inactive']);
    Budget::factory()->for($otherUser)->for($category)->active()->create();

    $this->actingAs($user, 'web')
        ->getJson('/api/v1/user/budgets')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $activeBudget->id);
});

test('admin can also list their own active budgets', function () {
    $admin = User::factory()->create();
    $admin->assignRole(Role::findOrCreate('admin', 'web'));

    $category = Category::factory()->create();
    $budget = Budget::factory()->for($admin)->for($category)->active()->create();

    $this->actingAs($admin, 'web')
        ->getJson('/api/v1/user/budgets')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $budget->id);
});

test('budget list calculates posted spending for the current period and user', function () {
    $user = User::factory()->create();
    $user->assignRole(Role::findOrCreate('user', 'web'));
    $otherUser = User::factory()->create();
    $otherUser->assignRole(Role::findOrCreate('user', 'web'));

    $wallet = UserWallet::factory()->for($user)->create();
    $otherWallet = UserWallet::factory()->for($otherUser)->create();
    $category = Category::factory()->create();
    $budget = Budget::factory()->for($user)->active()->monthly()->create([
        'category_id' => $category->id,
        'amount_limit' => 1000,
    ]);

    ExpenseTransaction::factory()->forUser($user)->expense()->posted()->create([
        'wallet_id' => $wallet->id,
        'category_id' => $category->id,
        'amount' => 300,
        'transacted_at' => '2026-07-10',
    ]);
    ExpenseTransaction::factory()->forUser($user)->expense()->pending()->create([
        'wallet_id' => $wallet->id,
        'category_id' => $category->id,
        'amount' => 200,
        'transacted_at' => '2026-07-11',
    ]);
    ExpenseTransaction::factory()->forUser($user)->expense()->posted()->create([
        'wallet_id' => $wallet->id,
        'category_id' => $category->id,
        'amount' => 400,
        'transacted_at' => '2026-06-30',
    ]);
    ExpenseTransaction::factory()->forUser($otherUser)->expense()->posted()->create([
        'wallet_id' => $otherWallet->id,
        'category_id' => $category->id,
        'amount' => 900,
        'transacted_at' => '2026-07-12',
    ]);

    $this->actingAs($user, 'web')
        ->getJson('/api/v1/user/budgets')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $budget->id)
        ->assertJsonPath('data.0.spent', 300)
        ->assertJsonPath('data.0.category.id', $category->id);
});

test('yearly budgets are calculated against the full year window', function () {
    $user = User::factory()->create();
    $user->assignRole(Role::findOrCreate('user', 'web'));
    $wallet = UserWallet::factory()->for($user)->create();
    $category = Category::factory()->create();
    $budget = Budget::factory()->for($user)->for($category)->active()->create([
        'period' => 'yearly',
        'amount_limit' => 5000,
    ]);

    ExpenseTransaction::factory()->forUser($user)->expense()->posted()->create([
        'wallet_id' => $wallet->id,
        'category_id' => $category->id,
        'amount' => 800,
        'transacted_at' => '2026-01-15',
    ]);
    ExpenseTransaction::factory()->forUser($user)->expense()->posted()->create([
        'wallet_id' => $wallet->id,
        'category_id' => $category->id,
        'amount' => 200,
        'transacted_at' => '2025-12-31',
    ]);

    $this->actingAs($user, 'web')
        ->getJson('/api/v1/user/budgets')
        ->assertOk()
        ->assertJsonPath('data.0.id', $budget->id)
        ->assertJsonPath('data.0.spent', 800);
});

test('budgets ordered by period then newest id', function () {
    $user = User::factory()->create();
    $user->assignRole(Role::findOrCreate('user', 'web'));
    $category1 = Category::factory()->create();
    $category2 = Category::factory()->create();

    $monthly = Budget::factory()->for($user)->for($category1)->active()->monthly()->create();
    $yearly = Budget::factory()->for($user)->for($category2)->active()->create(['period' => 'yearly']);

    $this->actingAs($user, 'web')
        ->getJson('/api/v1/user/budgets')
        ->assertOk()
        ->assertJsonPath('data.0.id', $monthly->id)
        ->assertJsonPath('data.1.id', $yearly->id);
});