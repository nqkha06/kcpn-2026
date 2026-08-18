<?php

use App\Models\UserWallet;
use Tests\Support\TestData;
use Tests\Support\TestResponseAssertions;

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

test('user wallet create follows shared execution data', function (array $case) {
    $user = regularUser();

    if (in_array('user_with_duplicate_wallet_exists', $case['preconditions'], true)) {
        UserWallet::factory()->for($user)->create(['name' => 'Duplicate Wallet']);
    }

    if (in_array('user_with_default_wallet_exists', $case['preconditions'], true)) {
        UserWallet::factory()->for($user)->defaultWallet()->create(['name' => 'Previous Default']);
    }

    if (in_array('other_user_wallet_with_same_name_exists', $case['preconditions'], true)) {
        UserWallet::factory()->for(regularUser())->create(['name' => 'Shared Wallet Name']);
    }

    $case = TestData::resolveAliases($case, ['user' => ['id' => $user->id]]);

    if ($case['actor'] === 'user') {
        $this->actingAs($user);
    }

    $beforeCount = UserWallet::query()->count();
    $response = $this->json(
        $case['request']['method'],
        $case['request']['endpoint'],
        $case['request']['body'],
        $case['request']['headers'],
    );

    TestResponseAssertions::assertForCase($response, $case);

    $expectedDelta = $case['expected']['database_change']['operation'] === 'insert' ? 1 : 0;
    expect(UserWallet::query()->count())->toBe($beforeCount + $expectedDelta);

    if ($expectedDelta === 1) {
        $wallet = UserWallet::query()->findOrFail($response->json('data.id'));

        expect($wallet->user->is($user))->toBeTrue();
        $this->assertEqualsWithDelta(
            (float) $wallet->opening_balance,
            (float) $response->json('data.current_balance'),
            0.001,
        );

        if ($wallet->is_default) {
            expect($user->wallets()->where('is_default', true)->count())->toBe(1);
        }
    }
})->with(TestData::load('user/wallets/create.json'));
