<?php

use App\Models\UserWallet;
use Tests\Support\TestData;
use Tests\Support\TestResponseAssertions;

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

test('user wallet update follows shared execution data', function (array $case) {
    $user = regularUser();
    $walletOwner = in_array('user_and_other_wallet_exists', $case['preconditions'], true)
        ? regularUser()
        : $user;
    $isOnlyDefault = in_array('user_with_only_default_wallet_exists', $case['preconditions'], true);
    $wallet = UserWallet::factory()->for($walletOwner)->create([
        'name' => 'Original Wallet',
        'opening_balance' => 25,
        'is_default' => $isOnlyDefault,
    ]);

    if (in_array('user_with_wallet_and_duplicate_wallet_exists', $case['preconditions'], true)) {
        UserWallet::factory()->for($user)->create(['name' => 'Duplicate Wallet']);
    }

    if (in_array('user_with_wallet_and_default_wallet_exists', $case['preconditions'], true)) {
        UserWallet::factory()->for($user)->defaultWallet()->create(['name' => 'Previous Default']);
    }

    $isMissingWallet = in_array('missing_wallet_alias', $case['preconditions'], true);
    $case = TestData::resolveAliases($case, [
        'user' => ['id' => $user->id],
        'wallet' => ['id' => $isMissingWallet ? 999_999_999 : $wallet->id],
    ]);

    if ($case['actor'] === 'user') {
        $this->actingAs($user);
    }

    $endpoint = $case['request']['endpoint'];
    foreach ($case['request']['path'] as $name => $value) {
        $endpoint = str_replace('{'.$name.'}', (string) $value, $endpoint);
    }

    $original = $wallet->only(['name', 'currency', 'opening_balance', 'is_default']);
    $response = $this->json(
        $case['request']['method'],
        $endpoint,
        $case['request']['body'],
        $case['request']['headers'],
    );

    TestResponseAssertions::assertForCase($response, $case);

    if ($case['expected']['database_change']['operation'] === 'update') {
        $updated = $wallet->fresh();

        expect($updated->user->is($user))->toBeTrue();
        $this->assertEqualsWithDelta(
            (float) $updated->opening_balance,
            (float) $response->json('data.current_balance'),
            0.001,
        );

        if ($updated->is_default) {
            expect($user->wallets()->where('is_default', true)->count())->toBe(1);
        }
    } else {
        expect($wallet->fresh()->only(array_keys($original)))->toBe($original);
    }
})->with(TestData::load('user/wallets/update.json'));
