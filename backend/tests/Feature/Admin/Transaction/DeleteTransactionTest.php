<?php

use App\Models\ExpenseTransaction;

test('an admin can delete a transaction', function () {
    $transaction = ExpenseTransaction::factory()->create();

    $this->actingAs(adminUser())
        ->deleteJson("/api/v1/admin/transactions/{$transaction->id}")
        ->assertOk();

    $this->assertDatabaseMissing('expense_transactions', ['id' => $transaction->id]);
});
