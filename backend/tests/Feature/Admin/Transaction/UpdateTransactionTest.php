<?php

use App\Models\ExpenseTransaction;
use App\Models\UserWallet;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\putJson;

test('an admin can update a transaction', function () {
    $transaction = ExpenseTransaction::factory()->create(['status' => 'posted']);

    actingAs(adminUser())
        ->putJson("/api/v1/admin/transactions/{$transaction->id}", [
            'user_id' => $transaction->user_id,
            'wallet_id' => $transaction->wallet_id,
            'category_id' => $transaction->category_id,
            'type' => 'expense',
            'amount' => 180000,
            'transacted_at' => '2026-07-30',
            'status' => 'pending',
            'note' => 'Admin updated',
            'labels' => ['updated'],
        ])
        ->assertOk()
        ->assertJsonPath('data.amount', 180000)
        ->assertJsonPath('data.status', 'pending');

    assertDatabaseHas('expense_transactions', ['id' => $transaction->id, 'status' => 'pending']);
});

test('a guest cannot update an admin transaction', function () {
    $transaction = ExpenseTransaction::factory()->create();

    putJson("/api/v1/admin/transactions/{$transaction->id}", [])
        ->assertUnauthorized();
});

test('a regular user cannot update a transaction through the admin endpoint', function () {
    $transaction = ExpenseTransaction::factory()->forUser(regularUser())->create();

    actingAs($transaction->user)
        ->putJson("/api/v1/admin/transactions/{$transaction->id}", [])
        ->assertForbidden();
});

test('admin transaction update validates required fields', function () {
    $transaction = ExpenseTransaction::factory()->create();

    actingAs(adminUser())
        ->putJson("/api/v1/admin/transactions/{$transaction->id}", [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors([
            'user_id',
            'wallet_id',
            'type',
            'amount',
            'transacted_at',
            'status',
        ]);
});

test('admin transaction update enforces the selected users wallet ownership', function () {
    $transaction = ExpenseTransaction::factory()->create();
    $selectedUser = regularUser();
    $otherWallet = UserWallet::factory()->for(regularUser())->create();

    actingAs(adminUser())
        ->putJson("/api/v1/admin/transactions/{$transaction->id}", [
            'user_id' => $selectedUser->id,
            'wallet_id' => $otherWallet->id,
            'type' => 'expense',
            'amount' => 100,
            'transacted_at' => '2026-08-01',
            'status' => 'posted',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('wallet_id');

    expect($transaction->fresh()->user_id)->not->toBe($selectedUser->id);
});

test('updating a missing admin transaction returns not found', function () {
    $user = regularUser();
    $wallet = UserWallet::factory()->for($user)->create();

    actingAs(adminUser())
        ->putJson('/api/v1/admin/transactions/999999', [
            'user_id' => $user->id,
            'wallet_id' => $wallet->id,
            'type' => 'expense',
            'amount' => 100,
            'transacted_at' => '2026-08-01',
            'status' => 'posted',
        ])
        ->assertNotFound();
});
