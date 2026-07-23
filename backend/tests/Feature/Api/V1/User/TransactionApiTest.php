<?php

use App\Models\Category;
use App\Models\ExpenseTransaction;
use App\Models\User;
use App\Models\UserWallet;
use Spatie\Permission\Models\Role;

function transactionApiUser(): User
{
    $user = User::factory()->create();
    $user->assignRole(Role::findOrCreate('user', 'web'));

    return $user;
}

beforeEach(function (): void {
    $this->withHeaders([
        'Accept' => 'application/json',
        'Origin' => 'http://localhost:3000',
        'Referer' => 'http://localhost:3000/user/transactions',
    ]);
});

test('transactions are scoped paginated searchable filterable and sortable', function () {
    $user = transactionApiUser();
    $otherUser = transactionApiUser();
    $wallet = UserWallet::factory()->for($user)->create();
    $otherWallet = UserWallet::factory()->for($otherUser)->create();
    $category = Category::factory()->create();

    $matching = ExpenseTransaction::factory()->forUser($user)->expense()->posted()->create([
        'wallet_id' => $wallet->id,
        'category_id' => $category->id,
        'note' => 'Weekly groceries',
        'amount' => 120,
        'transacted_at' => '2026-07-10',
    ]);
    ExpenseTransaction::factory()->forUser($user)->income()->posted()->create([
        'wallet_id' => $wallet->id,
        'category_id' => $category->id,
        'note' => 'Salary',
        'amount' => 5000,
        'transacted_at' => '2026-07-11',
    ]);
    ExpenseTransaction::factory()->forUser($otherUser)->expense()->posted()->create([
        'wallet_id' => $otherWallet->id,
        'category_id' => $category->id,
        'note' => 'Weekly groceries',
        'amount' => 90,
        'transacted_at' => '2026-07-10',
    ]);

    $this->actingAs($user, 'web')
        ->getJson('/api/v1/user/transactions?search=groceries&type=expense&date_from=2026-07-01&date_to=2026-07-31&sort=amount&direction=asc&per_page=1')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $matching->id)
        ->assertJsonPath('data.0.wallet.id', $wallet->id)
        ->assertJsonPath('meta.current_page', 1)
        ->assertJsonPath('meta.per_page', 1)
        ->assertJsonPath('meta.total', 1)
        ->assertJsonStructure(['links' => ['first', 'last', 'prev', 'next']]);
});

test('user can create a posted transaction and labels are normalized', function () {
    $user = transactionApiUser();
    $wallet = UserWallet::factory()->for($user)->create();
    $category = Category::factory()->create();

    $response = $this->actingAs($user, 'web')
        ->postJson('/api/v1/user/transactions', [
            'wallet_id' => $wallet->id,
            'category_id' => $category->id,
            'type' => 'expense',
            'amount' => 250000,
            'transacted_at' => '2026-07-22',
            'note' => '  Lunch  ',
            'labels' => 'food, food, office',
        ]);

    $response
        ->assertCreated()
        ->assertJsonPath('data.status', 'posted')
        ->assertJsonPath('data.note', 'Lunch')
        ->assertJsonPath('data.labels', ['food', 'office']);

    $this->assertDatabaseHas('expense_transactions', [
        'id' => $response->json('data.id'),
        'user_id' => $user->id,
        'wallet_id' => $wallet->id,
        'status' => 'posted',
    ]);
});

test('user cannot create a transaction against another users wallet', function () {
    $user = transactionApiUser();
    $otherUser = transactionApiUser();
    $otherWallet = UserWallet::factory()->for($otherUser)->create();

    $this->actingAs($user, 'web')
        ->postJson('/api/v1/user/transactions', [
            'wallet_id' => $otherWallet->id,
            'type' => 'expense',
            'amount' => 100,
            'transacted_at' => '2026-07-22',
        ])
        ->assertUnprocessable()
        ->assertJsonPath('success', false)
        ->assertJsonValidationErrors('wallet_id');
});

test('transaction query validation rejects invalid url state', function () {
    $user = transactionApiUser();

    $this->actingAs($user, 'web')
        ->getJson('/api/v1/user/transactions?date_from=22-07-2026&sort=user_id&per_page=500')
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['date_from', 'sort', 'per_page']);
});
