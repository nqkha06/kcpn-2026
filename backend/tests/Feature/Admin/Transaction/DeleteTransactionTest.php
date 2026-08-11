<?php

use App\Models\ExpenseTransaction;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;
use function Pest\Laravel\deleteJson;

test('an admin can delete a transaction', function () {
    $transaction = ExpenseTransaction::factory()->create();

    actingAs(adminUser())
        ->deleteJson("/api/v1/admin/transactions/{$transaction->id}")
        ->assertOk();

    assertDatabaseMissing('expense_transactions', ['id' => $transaction->id]);
});

test('a guest cannot delete an admin transaction', function () {
    $transaction = ExpenseTransaction::factory()->create();

    deleteJson("/api/v1/admin/transactions/{$transaction->id}")
        ->assertUnauthorized();

    assertDatabaseHas('expense_transactions', ['id' => $transaction->id]);
});

test('a regular user cannot delete a transaction through the admin endpoint', function () {
    $transaction = ExpenseTransaction::factory()->forUser(regularUser())->create();

    actingAs($transaction->user)
        ->deleteJson("/api/v1/admin/transactions/{$transaction->id}")
        ->assertForbidden();
});

test('deleting a missing admin transaction returns not found', function () {
    actingAs(adminUser())
        ->deleteJson('/api/v1/admin/transactions/999999')
        ->assertNotFound();
});
