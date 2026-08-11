<?php

use App\Models\UserWallet;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;
use function Pest\Laravel\postJson;

test('a user can create a wallet', function () {
    $user = regularUser();

    actingAs($user)
        ->postJson('/api/v1/user/wallets', [
            'name' => 'Ví API',
            'currency' => 'vnd',
            'opening_balance' => 500000,
            'is_default' => false,
        ])
        ->assertCreated()
        ->assertJsonPath('data.currency', 'VND')
        ->assertJsonPath('data.is_default', true);

    assertDatabaseHas('user_wallets', ['user_id' => $user->id, 'name' => 'Ví API']);
});

test('a guest cannot create a wallet', function () {
    postJson('/api/v1/user/wallets', [
        'name' => 'Guest Wallet',
        'currency' => 'VND',
    ])->assertUnauthorized();

    assertDatabaseMissing('user_wallets', ['name' => 'Guest Wallet']);
});

test('wallet creation validates required and numeric fields', function () {
    actingAs(regularUser())
        ->postJson('/api/v1/user/wallets', [
            'opening_balance' => 'not-a-number',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['name', 'currency', 'opening_balance']);
});

test('a user cannot create wallets with the same name', function () {
    $user = regularUser();
    UserWallet::factory()->for($user)->create(['name' => 'Cash']);

    actingAs($user)
        ->postJson('/api/v1/user/wallets', [
            'name' => 'Cash',
            'currency' => 'VND',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('name');

    expect($user->wallets()->where('name', 'Cash')->count())->toBe(1);
});

test('different users can use the same wallet name', function () {
    UserWallet::factory()->for(regularUser())->create(['name' => 'Cash']);
    $user = regularUser();

    actingAs($user)
        ->postJson('/api/v1/user/wallets', [
            'name' => 'Cash',
            'currency' => 'VND',
        ])
        ->assertCreated();

    assertDatabaseHas('user_wallets', [
        'user_id' => $user->id,
        'name' => 'Cash',
    ]);
});

test('creating a new default wallet unsets the previous default wallet', function () {
    $user = regularUser();
    $currentDefault = UserWallet::factory()->for($user)->defaultWallet()->create();

    actingAs($user)
        ->postJson('/api/v1/user/wallets', [
            'name' => 'New Default',
            'currency' => 'usd',
            'is_default' => true,
        ])
        ->assertCreated()
        ->assertJsonPath('data.is_default', true);

    expect($currentDefault->fresh()->is_default)->toBeFalse();
    assertDatabaseHas('user_wallets', [
        'user_id' => $user->id,
        'name' => 'New Default',
        'currency' => 'USD',
        'is_default' => true,
    ]);
});
