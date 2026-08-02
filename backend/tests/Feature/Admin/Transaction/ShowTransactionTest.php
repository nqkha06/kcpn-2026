<?php

use App\Models\ExpenseTransaction;

test('an admin can view a transaction', function () {
    $transaction = ExpenseTransaction::factory()->create();

    $this->actingAs(adminUser())
        ->getJson("/api/v1/admin/transactions/{$transaction->id}")
        ->assertOk()
        ->assertJsonPath('data.id', $transaction->id);
});
