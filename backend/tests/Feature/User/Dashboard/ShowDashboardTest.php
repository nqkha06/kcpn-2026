<?php

use App\Models\Category;
use App\Models\ExpenseTransaction;
use App\Models\User;
use App\Models\UserWallet;
use Spatie\Permission\Models\Role;

beforeEach(function (): void {
    $this->withHeaders([
        'Accept' => 'application/json',
        'Origin' => 'http://localhost:3000',
        'Referer' => 'http://localhost:3000/user/dashboard',
    ]);
});

test('guests cannot view the dashboard', function () {
    $this->getJson('/api/v1/user/dashboard')
        ->assertUnauthorized()
        ->assertJsonPath('message', 'Unauthenticated');
});

test('dashboard exposes the expected top level structure', function () {
    $user = User::factory()->create();
    $user->assignRole(Role::findOrCreate('user', 'web'));

    $this->actingAs($user, 'web')
        ->getJson('/api/v1/user/dashboard')
        ->assertOk()
        ->assertJsonStructure([
            'success',
            'message',
            'data' => ['categories', 'wallets', 'transactions'],
        ]);
});

test('dashboard returns only the authenticated users finance data', function () {
    $user = User::factory()->create();
    $user->assignRole(Role::findOrCreate('user', 'web'));
    $otherUser = User::factory()->create();
    $otherUser->assignRole(Role::findOrCreate('user', 'web'));

    $wallet = UserWallet::factory()->for($user)->defaultWallet()->create([
        'opening_balance' => 1000,
    ]);
    UserWallet::factory()->for($otherUser)->create();

    $category = Category::factory()->create();
    $transaction = ExpenseTransaction::factory()->forUser($user)->for($category)
        ->expense()->posted()->create([
            'wallet_id' => $wallet->id,
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

test('dashboard only exposes active categories', function () {
    $user = User::factory()->create();
    $user->assignRole(Role::findOrCreate('user', 'web'));

    $active = Category::factory()->create(['name' => 'Active']);
    Category::factory()->inactive()->create(['name' => 'Inactive']);

    $this->actingAs($user, 'web')
        ->getJson('/api/v1/user/dashboard')
        ->assertOk()
        ->assertJsonCount(1, 'data.categories')
        ->assertJsonPath('data.categories.0.id', $active->id);
});

test('admin can also view their own dashboard', function () {
    $admin = User::factory()->create();
    $admin->assignRole(Role::findOrCreate('admin', 'web'));

    $wallet = UserWallet::factory()->for($admin)->defaultWallet()->create();

    $this->actingAs($admin, 'web')
        ->getJson('/api/v1/user/dashboard')
        ->assertOk()
        ->assertJsonCount(1, 'data.wallets')
        ->assertJsonPath('data.wallets.0.id', $wallet->id);
});

test('dashboard transactions are ordered by most recent transacted date', function () {
    $user = User::factory()->create();
    $user->assignRole(Role::findOrCreate('user', 'web'));
    $wallet = UserWallet::factory()->for($user)->create();
    $category = Category::factory()->create();

    $older = ExpenseTransaction::factory()->forUser($user)->for($category)
        ->expense()->posted()->create([
            'wallet_id' => $wallet->id,
            'transacted_at' => '2026-01-01',
        ]);
    $newer = ExpenseTransaction::factory()->forUser($user)->for($category)
        ->expense()->posted()->create([
            'wallet_id' => $wallet->id,
            'transacted_at' => '2026-06-01',
        ]);

    $this->actingAs($user, 'web')
        ->getJson('/api/v1/user/dashboard')
        ->assertOk()
        ->assertJsonPath('data.transactions.0.id', $newer->id)
        ->assertJsonPath('data.transactions.1.id', $older->id);
});