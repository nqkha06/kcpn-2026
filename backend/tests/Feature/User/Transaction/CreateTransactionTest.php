<?php

use App\Models\Category;
use App\Models\ExpenseTransaction;
use App\Models\UserWallet;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;
use function Pest\Laravel\postJson;

test('a user can create a transaction', function () {
    $user = regularUser();
    $wallet = UserWallet::factory()->for($user)->create();
    $category = Category::factory()->create();

    actingAs($user)
        ->postJson('/api/v1/user/transactions', [
            'wallet_id' => $wallet->id,
            'category_id' => $category->id,
            'type' => 'expense',
            'amount' => 50000,
            'transacted_at' => '2026-07-29',
            'note' => 'Ăn trưa văn phòng',
            'labels' => ['ăn-uống', 'demo'],
        ])
        ->assertCreated()
        ->assertJsonPath('data.wallet_id', $wallet->id)
        ->assertJsonPath('data.status', 'posted');

    assertDatabaseHas('expense_transactions', ['user_id' => $user->id, 'amount' => 50000]);
});

test('a guest cannot create a transaction', function () {
    postJson('/api/v1/user/transactions', [
        'wallet_id' => 1,
        'type' => 'expense',
        'amount' => 50000,
        'transacted_at' => '2026-07-29',
    ])->assertUnauthorized();

    expect(ExpenseTransaction::query()->count())->toBe(0);
});

test('a user cannot create a transaction for another users wallet', function () {
    $wallet = UserWallet::factory()->for(regularUser())->create();

    actingAs(regularUser())
        ->postJson('/api/v1/user/transactions', [
            'wallet_id' => $wallet->id,
            'type' => 'expense',
            'amount' => 50000,
            'transacted_at' => '2026-07-29',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('wallet_id');

    assertDatabaseMissing('expense_transactions', ['wallet_id' => $wallet->id]);
});

test('a user cannot create a transaction for another users private category', function () {
    $user = regularUser();
    $wallet = UserWallet::factory()->for($user)->create();
    $category = Category::factory()->create(['user_id' => regularUser()->id]);

    actingAs($user)
        ->postJson('/api/v1/user/transactions', [
            'wallet_id' => $wallet->id,
            'category_id' => $category->id,
            'type' => 'expense',
            'amount' => 50000,
            'transacted_at' => '2026-07-29',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('category_id');
});

test('a user cannot create a transaction for an inactive category', function () {
    $user = regularUser();
    $wallet = UserWallet::factory()->for($user)->create();
    $category = Category::factory()->inactive()->create();

    actingAs($user)
        ->postJson('/api/v1/user/transactions', [
            'wallet_id' => $wallet->id,
            'category_id' => $category->id,
            'type' => 'expense',
            'amount' => 50000,
            'transacted_at' => '2026-07-29',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('category_id');
});

test('transaction creation validates required fields', function () {
    actingAs(regularUser())
        ->postJson('/api/v1/user/transactions', [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['wallet_id', 'type', 'amount', 'transacted_at']);
});

test('transaction creation validates amount type date and label length', function () {
    $user = regularUser();
    $wallet = UserWallet::factory()->for($user)->create();

    actingAs($user)
        ->postJson('/api/v1/user/transactions', [
            'wallet_id' => $wallet->id,
            'type' => 'transfer',
            'amount' => 0,
            'transacted_at' => 'not-a-date',
            'labels' => [str_repeat('a', 31)],
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['type', 'amount', 'transacted_at', 'labels.0']);
});

test('transaction creation normalizes empty category note and labels', function () {
    $user = regularUser();
    $wallet = UserWallet::factory()->for($user)->create();

    $response = actingAs($user)
        ->postJson('/api/v1/user/transactions', [
            'wallet_id' => $wallet->id,
            'category_id' => 'none',
            'type' => 'income',
            'amount' => 100000,
            'transacted_at' => '2026-07-29',
            'status' => 'pending',
            'note' => '   ',
            'labels' => ' salary, recurring, salary, ',
        ])
        ->assertCreated()
        ->assertJsonPath('data.category_id', null)
        ->assertJsonPath('data.status', 'posted')
        ->assertJsonPath('data.note', null)
        ->assertJsonPath('data.labels', ['salary', 'recurring']);

    $transactionId = $response->json('data.id');

    assertDatabaseHas('expense_transactions', [
        'id' => $transactionId,
        'category_id' => null,
        'status' => 'posted',
        'note' => null,
    ]);
});
