<?php

use App\Models\ExpenseTransaction;
use App\Models\UserWallet;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\deleteJson;

test('a user can delete their wallet', function () {
    $user = regularUser();
    $wallet = UserWallet::factory()->for($user)->create();

    actingAs($user)
        ->deleteJson("/api/v1/user/wallets/{$wallet->id}")
        ->assertOk();

    expect(UserWallet::withTrashed()->find($wallet->id)?->trashed())->toBeTrue();
});

test('a guest cannot delete a wallet', function () {
    $wallet = UserWallet::factory()->create();

    deleteJson("/api/v1/user/wallets/{$wallet->id}")
        ->assertUnauthorized();

    expect($wallet->fresh()->trashed())->toBeFalse();
});

test('a user cannot delete another users wallet', function () {
    $wallet = UserWallet::factory()->for(regularUser())->create();

    actingAs(regularUser())
        ->deleteJson("/api/v1/user/wallets/{$wallet->id}")
        ->assertForbidden();

    expect($wallet->fresh()->trashed())->toBeFalse();
});

test('deleting the default wallet promotes the oldest remaining wallet', function () {
    $user = regularUser();
    $defaultWallet = UserWallet::factory()->for($user)->defaultWallet()->create();
    $oldestRemaining = UserWallet::factory()->for($user)->create();
    $newestRemaining = UserWallet::factory()->for($user)->create();

    actingAs($user)
        ->deleteJson("/api/v1/user/wallets/{$defaultWallet->id}")
        ->assertOk();

    expect($oldestRemaining->fresh()->is_default)->toBeTrue()
        ->and($newestRemaining->fresh()->is_default)->toBeFalse();
});

test('deleting a wallet keeps its transactions', function () {
    $user = regularUser();
    $wallet = UserWallet::factory()->for($user)->create();
    $transaction = ExpenseTransaction::factory()->forUser($user)->create([
        'wallet_id' => $wallet->id,
    ]);

    actingAs($user)
        ->deleteJson("/api/v1/user/wallets/{$wallet->id}")
        ->assertOk();

    assertDatabaseHas('expense_transactions', ['id' => $transaction->id]);
});

test('deleting a missing wallet returns not found', function () {
    actingAs(regularUser())
        ->deleteJson('/api/v1/user/wallets/999999')
        ->assertNotFound();
});
