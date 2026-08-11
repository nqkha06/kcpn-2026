<?php

use App\Models\ExpenseTransaction;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\getJson;

test('an admin can view a transaction', function () {
    $transaction = ExpenseTransaction::factory()->create();

    actingAs(adminUser())
        ->getJson("/api/v1/admin/transactions/{$transaction->id}")
        ->assertOk()
        ->assertJsonPath('data.id', $transaction->id);
});

test('a guest cannot view an admin transaction', function () {
    $transaction = ExpenseTransaction::factory()->create();

    getJson("/api/v1/admin/transactions/{$transaction->id}")
        ->assertUnauthorized();
});

test('a regular user cannot view a transaction through the admin endpoint', function () {
    $transaction = ExpenseTransaction::factory()->forUser(regularUser())->create();

    actingAs($transaction->user)
        ->getJson("/api/v1/admin/transactions/{$transaction->id}")
        ->assertForbidden();
});

test('viewing a missing admin transaction returns not found', function () {
    actingAs(adminUser())
        ->getJson('/api/v1/admin/transactions/999999')
        ->assertNotFound();
});
