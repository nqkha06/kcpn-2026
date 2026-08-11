<?php

use App\Models\UserWallet;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;
use function Pest\Laravel\patchJson;

test('a user can update their wallet', function () {
    $user = regularUser();
    $wallet = UserWallet::factory()->for($user)->create();

    actingAs($user)
        ->patchJson("/api/v1/user/wallets/{$wallet->id}", [
            'name' => 'Ví API Updated',
            'currency' => 'USD',
            'opening_balance' => 750000,
            'is_default' => true,
        ])
        ->assertOk()
        ->assertJsonPath('data.name', 'Ví API Updated')
        ->assertJsonPath('data.currency', 'USD');

    assertDatabaseHas('user_wallets', ['id' => $wallet->id, 'name' => 'Ví API Updated']);
});

test('a guest cannot update a wallet', function () {
    $wallet = UserWallet::factory()->create();

    patchJson("/api/v1/user/wallets/{$wallet->id}", [
        'name' => 'Guest Update',
        'currency' => 'VND',
    ])->assertUnauthorized();

    assertDatabaseMissing('user_wallets', [
        'id' => $wallet->id,
        'name' => 'Guest Update',
    ]);
});

test('a user cannot update another users wallet', function () {
    $wallet = UserWallet::factory()->for(regularUser())->create();

    actingAs(regularUser())
        ->patchJson("/api/v1/user/wallets/{$wallet->id}", [
            'name' => 'Stolen Wallet',
            'currency' => 'VND',
        ])
        ->assertForbidden();

    assertDatabaseMissing('user_wallets', [
        'id' => $wallet->id,
        'name' => 'Stolen Wallet',
    ]);
});

test('wallet update validates duplicate names within the same account', function () {
    $user = regularUser();
    UserWallet::factory()->for($user)->create(['name' => 'Cash']);
    $wallet = UserWallet::factory()->for($user)->create(['name' => 'Bank']);

    actingAs($user)
        ->patchJson("/api/v1/user/wallets/{$wallet->id}", [
            'name' => 'Cash',
            'currency' => 'VND',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('name');

    expect($wallet->fresh()->name)->toBe('Bank');
});

test('the only default wallet remains default when it is updated as non default', function () {
    $user = regularUser();
    $wallet = UserWallet::factory()->for($user)->defaultWallet()->create();

    actingAs($user)
        ->patchJson("/api/v1/user/wallets/{$wallet->id}", [
            'name' => $wallet->name,
            'currency' => $wallet->currency,
            'opening_balance' => $wallet->opening_balance,
            'is_default' => false,
        ])
        ->assertOk()
        ->assertJsonPath('data.is_default', true);

    expect($wallet->fresh()->is_default)->toBeTrue();
});

test('updating a wallet as default unsets the previous default wallet', function () {
    $user = regularUser();
    $currentDefault = UserWallet::factory()->for($user)->defaultWallet()->create();
    $wallet = UserWallet::factory()->for($user)->create();

    actingAs($user)
        ->patchJson("/api/v1/user/wallets/{$wallet->id}", [
            'name' => $wallet->name,
            'currency' => $wallet->currency,
            'opening_balance' => $wallet->opening_balance,
            'is_default' => true,
        ])
        ->assertOk()
        ->assertJsonPath('data.is_default', true);

    expect($currentDefault->fresh()->is_default)->toBeFalse();
});

test('updating a missing wallet returns not found', function () {
    actingAs(regularUser())
        ->patchJson('/api/v1/user/wallets/999999', [
            'name' => 'Missing Wallet',
            'currency' => 'VND',
        ])
        ->assertNotFound();
});
