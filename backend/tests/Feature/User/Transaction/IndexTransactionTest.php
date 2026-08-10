<?php

use App\Models\ExpenseTransaction;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\getJson;

test('a user can list only their transactions', function () {
    $user = regularUser();
    $otherUser = regularUser();
    $visible = ExpenseTransaction::factory()->forUser($user)->create();
    ExpenseTransaction::factory()->forUser($otherUser)->create();

    actingAs($user)
        ->getJson('/api/v1/user/transactions')
        ->assertOk()
        ->assertJsonPath('meta.total', 1)
        ->assertJsonPath('data.0.id', $visible->id);
});

test('a guest cannot list transactions', function () {
    getJson('/api/v1/user/transactions')->assertUnauthorized();
});

test('a user can search transactions by note', function () {
    $user = regularUser();
    $matching = ExpenseTransaction::factory()->forUser($user)->create(['note' => 'Office lunch']);
    ExpenseTransaction::factory()->forUser($user)->create(['note' => 'Bus ticket']);

    actingAs($user)
        ->getJson('/api/v1/user/transactions?search=lunch')
        ->assertOk()
        ->assertJsonPath('meta.total', 1)
        ->assertJsonPath('data.0.id', $matching->id);
});

test('a user can filter transactions by type status wallet and category', function () {
    $user = regularUser();
    $wallet = \App\Models\UserWallet::factory()->for($user)->create();
    $category = \App\Models\Category::factory()->create();
    $matching = ExpenseTransaction::factory()->forUser($user)->expense()->posted()->create([
        'wallet_id' => $wallet->id,
        'category_id' => $category->id,
    ]);
    ExpenseTransaction::factory()->forUser($user)->income()->pending()->create();

    $query = http_build_query([
        'type' => 'expense',
        'status' => 'posted',
        'wallet_id' => $wallet->id,
        'category_id' => $category->id,
    ]);

    actingAs($user)
        ->getJson('/api/v1/user/transactions?'.$query)
        ->assertOk()
        ->assertJsonPath('meta.total', 1)
        ->assertJsonPath('data.0.id', $matching->id);
});

test('a user can filter transactions by an inclusive date range', function () {
    $user = regularUser();
    $fromBoundary = ExpenseTransaction::factory()->forUser($user)->create(['transacted_at' => '2026-07-01']);
    $toBoundary = ExpenseTransaction::factory()->forUser($user)->create(['transacted_at' => '2026-07-31']);
    ExpenseTransaction::factory()->forUser($user)->create(['transacted_at' => '2026-08-01']);

    actingAs($user)
        ->getJson('/api/v1/user/transactions?date_from=2026-07-01&date_to=2026-07-31')
        ->assertOk()
        ->assertJsonPath('meta.total', 2)
        ->assertJsonFragment(['id' => $fromBoundary->id])
        ->assertJsonFragment(['id' => $toBoundary->id]);
});

test('a user can sort and paginate transactions', function () {
    $user = regularUser();
    ExpenseTransaction::factory()->forUser($user)->create(['amount' => 300]);
    $lowest = ExpenseTransaction::factory()->forUser($user)->create(['amount' => 100]);
    ExpenseTransaction::factory()->forUser($user)->create(['amount' => 200]);

    actingAs($user)
        ->getJson('/api/v1/user/transactions?sort=amount&direction=asc&per_page=2')
        ->assertOk()
        ->assertJsonPath('data.0.id', $lowest->id)
        ->assertJsonPath('meta.per_page', 2)
        ->assertJsonPath('meta.total', 3)
        ->assertJsonPath('meta.last_page', 2);
});

test('transaction list query parameters are validated', function () {
    actingAs(regularUser())
        ->getJson('/api/v1/user/transactions?type=transfer&status=cancelled&date_from=07-01-2026&date_to=2026-06-01&sort=id&direction=sideways&per_page=101')
        ->assertUnprocessable()
        ->assertJsonValidationErrors([
            'type',
            'status',
            'date_from',
            'sort',
            'direction',
            'per_page',
        ]);
});

test('transaction list rejects an end date before the start date', function () {
    actingAs(regularUser())
        ->getJson('/api/v1/user/transactions?date_from=2026-07-31&date_to=2026-07-01')
        ->assertUnprocessable()
        ->assertJsonValidationErrors('date_to');
});

test('a user cannot filter transactions by another users wallet', function () {
    $wallet = \App\Models\UserWallet::factory()->for(regularUser())->create();

    actingAs(regularUser())
        ->getJson('/api/v1/user/transactions?wallet_id='.$wallet->id)
        ->assertUnprocessable()
        ->assertJsonValidationErrors('wallet_id');
});
