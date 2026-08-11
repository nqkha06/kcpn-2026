<?php

use App\Models\Category;
use App\Models\ExpenseTransaction;
use App\Models\User;
use App\Models\UserWallet;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\getJson;
use function Pest\Laravel\withHeaders;

beforeEach(function (): void {
    withHeaders([
        'Accept' => 'application/json',
        'Origin' => 'http://localhost:3000',
        'Referer' => 'http://localhost:3000/user/dashboard',
    ]);
});

test('guests cannot view the dashboard', function () {
    getJson('/api/v1/user/dashboard')
        ->assertUnauthorized()
        ->assertJsonPath('message', 'Unauthenticated');
});

test('dashboard exposes the expected top level structure', function () {
    $user = User::factory()->create();
    $user->assignRole(Role::findOrCreate('user', 'web'));

    actingAs($user, 'web')
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

    actingAs($user, 'web')
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

    actingAs($user, 'web')
        ->getJson('/api/v1/user/dashboard')
        ->assertOk()
        ->assertJsonCount(1, 'data.categories')
        ->assertJsonPath('data.categories.0.id', $active->id);
});

test('dashboard does not expose another users private categories', function () {
    $user = regularUser();
    $visible = Category::factory()->create([
        'user_id' => $user->id,
        'name' => 'Visible Private',
    ]);
    Category::factory()->create([
        'user_id' => regularUser()->id,
        'name' => 'Hidden Private',
    ]);

    actingAs($user, 'web')
        ->getJson('/api/v1/user/dashboard')
        ->assertOk()
        ->assertJsonFragment(['id' => $visible->id])
        ->assertJsonMissing(['name' => 'Hidden Private']);
});

test('dashboard wallet balance ignores pending transactions', function () {
    $user = regularUser();
    $wallet = UserWallet::factory()->for($user)->create(['opening_balance' => 1000]);
    ExpenseTransaction::factory()->forUser($user)->expense()->pending()->create([
        'wallet_id' => $wallet->id,
        'amount' => 900,
    ]);

    actingAs($user, 'web')
        ->getJson('/api/v1/user/dashboard')
        ->assertOk()
        ->assertJsonPath('data.wallets.0.current_balance', 1000);
});

test('dashboard returns empty finance collections for a new user', function () {
    actingAs(regularUser(), 'web')
        ->getJson('/api/v1/user/dashboard')
        ->assertOk()
        ->assertJsonCount(0, 'data.wallets')
        ->assertJsonCount(0, 'data.transactions');
});

test('admin can also view their own dashboard', function () {
    $admin = User::factory()->create();
    $admin->assignRole(Role::findOrCreate('admin', 'web'));

    $wallet = UserWallet::factory()->for($admin)->defaultWallet()->create();

    actingAs($admin, 'web')
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

    actingAs($user, 'web')
        ->getJson('/api/v1/user/dashboard')
        ->assertOk()
        ->assertJsonPath('data.transactions.0.id', $newer->id)
        ->assertJsonPath('data.transactions.1.id', $older->id);
});
