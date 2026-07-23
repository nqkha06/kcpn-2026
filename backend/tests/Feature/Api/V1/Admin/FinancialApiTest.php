<?php

use App\Models\Budget;
use App\Models\Category;
use App\Models\ExpenseTransaction;
use App\Models\User;
use App\Models\UserWallet;
use Spatie\Permission\Models\Role;

function financialApiActor(string $role): User
{
    $user = User::factory()->create();
    $user->assignRole(Role::findOrCreate($role, 'web'));

    return $user;
}

beforeEach(function (): void {
    $this->withHeaders([
        'Accept' => 'application/json',
        'Origin' => 'http://localhost:3000',
        'Referer' => 'http://localhost:3000/admin',
    ]);
});

test('admin financial routes reject guests and non admin users', function () {
    $this->getJson('/api/v1/admin/dashboard')
        ->assertUnauthorized()
        ->assertJsonPath('message', 'Unauthenticated');

    $this->actingAs(financialApiActor('user'), 'web')
        ->getJson('/api/v1/admin/budgets')
        ->assertForbidden()
        ->assertJsonPath('message', 'Forbidden');
});

test('admin dashboard exposes current financial statistics and recent data', function () {
    $admin = financialApiActor('admin');
    $customer = User::factory()->create();
    $wallet = UserWallet::factory()->for($customer)->create();
    $category = Category::factory()->create(['name' => 'Dashboard category']);
    Budget::factory()->for($customer)->for($category)->active()->create();
    ExpenseTransaction::factory()->for($customer)->for($wallet, 'wallet')->for($category)
        ->income()->posted()->create([
            'amount' => 1000,
            'transacted_at' => now()->toDateString(),
        ]);
    ExpenseTransaction::factory()->for($customer)->for($wallet, 'wallet')->for($category)
        ->expense()->posted()->create([
            'amount' => 250,
            'transacted_at' => now()->toDateString(),
        ]);

    $this->actingAs($admin, 'web')
        ->getJson('/api/v1/admin/dashboard')
        ->assertOk()
        ->assertJsonPath('data.stats.activeBudgets', 1)
        ->assertJsonPath('data.stats.postedIncomeThisMonth', 1000)
        ->assertJsonPath('data.stats.postedExpenseThisMonth', 250)
        ->assertJsonPath('data.stats.netThisMonth', 750)
        ->assertJsonCount(6, 'data.monthlyFlow')
        ->assertJsonPath('data.topExpenseCategories.0.id', $category->id)
        ->assertJsonCount(2, 'data.recentTransactions');
});

test('admin can perform budget crud with filters options and spent amount', function () {
    $admin = financialApiActor('admin');
    $customer = User::factory()->create(['name' => 'Budget Customer']);
    $wallet = UserWallet::factory()->for($customer)->create();
    $category = Category::factory()->create(['name' => 'Food Budget']);
    ExpenseTransaction::factory()->for($customer)->for($wallet, 'wallet')->for($category)
        ->expense()->posted()->create([
            'amount' => 125.50,
            'transacted_at' => now()->toDateString(),
        ]);
    $this->actingAs($admin, 'web');

    $created = $this->postJson('/api/v1/admin/budgets', [
        'user_id' => $customer->id,
        'category_id' => $category->id,
        'amount_limit' => 1000,
        'period' => 'monthly',
        'status' => 'active',
        'note' => ' Monthly food ',
    ])
        ->assertCreated()
        ->assertJsonPath('data.amount_limit', 1000)
        ->assertJsonPath('data.spent', 125.5)
        ->assertJsonPath('data.note', 'Monthly food');

    $budgetId = $created->json('data.id');

    $this->getJson('/api/v1/admin/budgets?search=Budget%20Customer&period=monthly&status=active&user_id='.$customer->id.'&category_id='.$category->id.'&sort=amount_limit&direction=asc')
        ->assertOk()
        ->assertJsonPath('meta.total', 1)
        ->assertJsonPath('data.0.id', $budgetId);

    $this->getJson('/api/v1/admin/budgets/options')
        ->assertOk()
        ->assertJsonFragment(['id' => $customer->id, 'name' => 'Budget Customer']);

    $this->patchJson('/api/v1/admin/budgets/'.$budgetId, [
        'user_id' => $customer->id,
        'category_id' => $category->id,
        'amount_limit' => 1500,
        'period' => 'yearly',
        'status' => 'inactive',
        'note' => '',
    ])
        ->assertOk()
        ->assertJsonPath('data.amount_limit', 1500)
        ->assertJsonPath('data.period', 'yearly')
        ->assertJsonPath('data.note', null);

    $this->deleteJson('/api/v1/admin/budgets/'.$budgetId)
        ->assertOk()
        ->assertJsonPath('message', 'Budget deleted successfully');

    $this->assertDatabaseMissing('budgets', ['id' => $budgetId]);
});

test('budget api enforces uniqueness and query validation', function () {
    $admin = financialApiActor('admin');
    $customer = User::factory()->create();
    $category = Category::factory()->create();
    Budget::factory()->for($customer)->for($category)->monthly()->create();
    $this->actingAs($admin, 'web');

    $this->postJson('/api/v1/admin/budgets', [
        'user_id' => $customer->id,
        'category_id' => $category->id,
        'amount_limit' => 200,
        'period' => 'monthly',
        'status' => 'active',
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['category_id']);

    $this->getJson('/api/v1/admin/budgets?period=weekly&sort=user_id&per_page=200')
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['period', 'sort', 'per_page']);
});

test('admin can perform transaction crud and normalize labels', function () {
    $admin = financialApiActor('admin');
    $customer = User::factory()->create(['name' => 'Transaction Customer']);
    $wallet = UserWallet::factory()->for($customer)->create(['name' => 'Primary Wallet']);
    $category = Category::factory()->create(['name' => 'Travel']);
    $this->actingAs($admin, 'web');

    $created = $this->postJson('/api/v1/admin/transactions', [
        'user_id' => $customer->id,
        'wallet_id' => $wallet->id,
        'category_id' => $category->id,
        'type' => 'expense',
        'amount' => 42.75,
        'transacted_at' => now()->toDateString(),
        'status' => 'posted',
        'note' => ' Taxi ',
        'labels' => 'travel, travel, work',
    ])
        ->assertCreated()
        ->assertJsonPath('data.amount', 42.75)
        ->assertJsonPath('data.labels', ['travel', 'work'])
        ->assertJsonPath('data.wallet.id', $wallet->id);

    $transactionId = $created->json('data.id');

    $this->getJson('/api/v1/admin/transactions?search=Taxi&type=expense&status=posted&user_id='.$customer->id.'&wallet_id='.$wallet->id.'&category_id='.$category->id.'&from_date='.now()->toDateString().'&to_date='.now()->toDateString())
        ->assertOk()
        ->assertJsonPath('meta.total', 1)
        ->assertJsonPath('data.0.id', $transactionId);

    $this->getJson('/api/v1/admin/transactions/options')
        ->assertOk()
        ->assertJsonFragment(['id' => $wallet->id, 'name' => 'Primary Wallet']);

    $this->patchJson('/api/v1/admin/transactions/'.$transactionId, [
        'user_id' => $customer->id,
        'wallet_id' => $wallet->id,
        'category_id' => null,
        'type' => 'income',
        'amount' => 100,
        'transacted_at' => now()->subDay()->toDateString(),
        'status' => 'pending',
        'note' => '',
        'labels' => [],
    ])
        ->assertOk()
        ->assertJsonPath('data.type', 'income')
        ->assertJsonPath('data.category_id', null)
        ->assertJsonPath('data.labels', []);

    $this->deleteJson('/api/v1/admin/transactions/'.$transactionId)
        ->assertOk()
        ->assertJsonPath('message', 'Transaction deleted successfully');

    $this->assertDatabaseMissing('expense_transactions', ['id' => $transactionId]);
});

test('transaction api validates wallet ownership and list ranges', function () {
    $admin = financialApiActor('admin');
    $customer = User::factory()->create();
    $otherCustomer = User::factory()->create();
    $otherWallet = UserWallet::factory()->for($otherCustomer)->create();
    $this->actingAs($admin, 'web');

    $this->postJson('/api/v1/admin/transactions', [
        'user_id' => $customer->id,
        'wallet_id' => $otherWallet->id,
        'type' => 'expense',
        'amount' => 10,
        'transacted_at' => now()->toDateString(),
        'status' => 'posted',
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['wallet_id']);

    $this->getJson('/api/v1/admin/transactions?type=transfer&from_date=2026-02-02&to_date=2026-01-01&sort=wallet_id')
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['type', 'to_date', 'sort']);
});
