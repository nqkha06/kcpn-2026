<?php

use App\Models\ExpenseTransaction;
use App\Models\UserWallet;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\getJson;

test('a user can list only their wallets', function () {
    $user = regularUser();
    $wallet = UserWallet::factory()->for($user)->create();
    UserWallet::factory()->for(regularUser())->create();

    actingAs($user)
        ->getJson('/api/v1/user/wallets')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $wallet->id);
});

test('a guest cannot list wallets', function () {
    getJson('/api/v1/user/wallets')->assertUnauthorized();
});

test('wallets are ordered with the default wallet first then by name', function () {
    $user = regularUser();
    $beta = UserWallet::factory()->for($user)->create(['name' => 'Beta']);
    $default = UserWallet::factory()->for($user)->defaultWallet()->create(['name' => 'Zulu']);
    $alpha = UserWallet::factory()->for($user)->create(['name' => 'Alpha']);

    actingAs($user)
        ->getJson('/api/v1/user/wallets')
        ->assertOk()
        ->assertJsonPath('data.0.id', $default->id)
        ->assertJsonPath('data.1.id', $alpha->id)
        ->assertJsonPath('data.2.id', $beta->id);
});

test('wallet balance includes only posted income and expenses', function () {
    $user = regularUser();
    $wallet = UserWallet::factory()->for($user)->create(['opening_balance' => 1000]);

    ExpenseTransaction::factory()->forUser($user)->income()->posted()->create([
        'wallet_id' => $wallet->id,
        'amount' => 500,
    ]);
    ExpenseTransaction::factory()->forUser($user)->expense()->posted()->create([
        'wallet_id' => $wallet->id,
        'amount' => 200,
    ]);
    ExpenseTransaction::factory()->forUser($user)->income()->pending()->create([
        'wallet_id' => $wallet->id,
        'amount' => 900,
    ]);

    actingAs($user)
        ->getJson('/api/v1/user/wallets')
        ->assertOk()
        ->assertJsonPath('data.0.current_balance', 1300);
});
