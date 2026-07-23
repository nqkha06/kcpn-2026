<?php

use App\Models\Budget;
use App\Models\Category;
use App\Models\ExpenseTransaction;
use App\Models\User;
use App\Models\UserWallet;
use Illuminate\Support\Carbon;
use Spatie\Permission\Models\Role;

function budgetApiUser(): User
{
    $user = User::factory()->create();
    $user->assignRole(Role::findOrCreate('user', 'web'));

    return $user;
}

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

test('budget list calculates posted spending for the current period and user', function () {
    $user = budgetApiUser();
    $otherUser = budgetApiUser();
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

test('user can create one budget per category and period', function () {
    $user = budgetApiUser();
    $category = Category::factory()->create();
    $this->actingAs($user, 'web');

    $payload = [
        'category_id' => $category->id,
        'amount_limit' => 2500000,
        'period' => 'yearly',
        'note' => '  Annual target  ',
    ];

    $this->postJson('/api/v1/user/budgets', $payload)
        ->assertCreated()
        ->assertJsonPath('data.period', 'yearly')
        ->assertJsonPath('data.note', 'Annual target')
        ->assertJsonPath('data.spent', 0);

    $this->postJson('/api/v1/user/budgets', $payload)
        ->assertUnprocessable()
        ->assertJsonValidationErrors('category_id');
});

test('dashboard returns only the authenticated users finance data', function () {
    $user = budgetApiUser();
    $otherUser = budgetApiUser();
    $wallet = UserWallet::factory()->for($user)->defaultWallet()->create([
        'opening_balance' => 1000,
    ]);
    UserWallet::factory()->for($otherUser)->create();
    $category = Category::factory()->create();
    $transaction = ExpenseTransaction::factory()->forUser($user)->expense()->posted()->create([
        'wallet_id' => $wallet->id,
        'category_id' => $category->id,
        'amount' => 250,
    ]);
    ExpenseTransaction::factory()->forUser($otherUser)->expense()->posted()->create();

    $this->actingAs($user, 'web')
        ->getJson('/api/v1/user/dashboard')
        ->assertOk()
        ->assertJsonCount(1, 'data.wallets')
        ->assertJsonCount(1, 'data.transactions')
        ->assertJsonPath('data.wallets.0.id', $wallet->id)
        ->assertJsonPath('data.wallets.0.current_balance', 750)
        ->assertJsonPath('data.transactions.0.id', $transaction->id);
});

test('only active categories are exposed to finance pages', function () {
    $user = budgetApiUser();
    $active = Category::factory()->create(['name' => 'Active']);
    Category::factory()->inactive()->create(['name' => 'Inactive']);

    $this->actingAs($user, 'web')
        ->getJson('/api/v1/user/categories')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $active->id);
});
