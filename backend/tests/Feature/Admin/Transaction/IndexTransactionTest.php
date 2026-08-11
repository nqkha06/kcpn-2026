<?php

use App\Models\ExpenseTransaction;
use App\Models\UserWallet;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\getJson;

test('an admin can list transactions', function () {
    $transaction = ExpenseTransaction::factory()->create();

    actingAs(adminUser())
        ->getJson('/api/v1/admin/transactions')
        ->assertOk()
        ->assertJsonPath('meta.total', 1)
        ->assertJsonPath('data.0.id', $transaction->id);
});

test('a guest cannot list admin transactions', function () {
    getJson('/api/v1/admin/transactions')->assertUnauthorized();
});

test('a regular user cannot list admin transactions', function () {
    actingAs(regularUser())
        ->getJson('/api/v1/admin/transactions')
        ->assertForbidden();
});

test('an admin can search transactions by related data', function () {
    $user = regularUser();
    $wallet = UserWallet::factory()->for($user)->create(['name' => 'Emergency Wallet']);
    $matching = ExpenseTransaction::factory()->forUser($user)->create([
        'wallet_id' => $wallet->id,
        'note' => 'Annual insurance',
    ]);
    ExpenseTransaction::factory()->create(['note' => 'Groceries']);

    actingAs(adminUser())
        ->getJson('/api/v1/admin/transactions?search=Emergency')
        ->assertOk()
        ->assertJsonPath('meta.total', 1)
        ->assertJsonPath('data.0.id', $matching->id);
});

test('an admin can search transactions by id', function () {
    $transaction = ExpenseTransaction::factory()->create();

    actingAs(adminUser())
        ->getJson("/api/v1/admin/transactions?search={$transaction->id}")
        ->assertOk()
        ->assertJsonPath('meta.total', 1)
        ->assertJsonPath('data.0.id', $transaction->id);
})->todo('AdminTransactionService calls the undefined Eloquent Builder method orWhereKey');

test('an admin can filter transactions by business fields and inclusive dates', function () {
    $user = regularUser();
    $wallet = UserWallet::factory()->for($user)->create();
    $matching = ExpenseTransaction::factory()->forUser($user)->create([
        'wallet_id' => $wallet->id,
        'type' => 'expense',
        'status' => 'posted',
        'transacted_at' => '2026-08-01',
    ]);
    ExpenseTransaction::factory()->forUser($user)->create([
        'wallet_id' => $wallet->id,
        'type' => 'income',
        'status' => 'posted',
        'transacted_at' => '2026-08-01',
    ]);

    actingAs(adminUser())
        ->getJson("/api/v1/admin/transactions?user_id={$user->id}&wallet_id={$wallet->id}&type=expense&status=posted&from_date=2026-08-01&to_date=2026-08-01")
        ->assertOk()
        ->assertJsonPath('meta.total', 1)
        ->assertJsonPath('data.0.id', $matching->id);
});

test('an admin can sort and paginate transactions', function () {
    ExpenseTransaction::factory()->create(['amount' => 200]);
    $cheapest = ExpenseTransaction::factory()->create(['amount' => 100]);

    actingAs(adminUser())
        ->getJson('/api/v1/admin/transactions?sort=amount&direction=asc&per_page=1')
        ->assertOk()
        ->assertJsonPath('meta.total', 2)
        ->assertJsonPath('meta.per_page', 1)
        ->assertJsonPath('data.0.id', $cheapest->id);
});

test('admin transaction list query parameters are validated', function () {
    actingAs(adminUser())
        ->getJson('/api/v1/admin/transactions?type=transfer&status=approved&from_date=2026-08-02&to_date=2026-08-01&sort=note&direction=sideways&per_page=101')
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['type', 'status', 'to_date', 'sort', 'direction', 'per_page']);
});
