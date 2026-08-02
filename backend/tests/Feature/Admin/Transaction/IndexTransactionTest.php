<?php

use App\Models\ExpenseTransaction;

test('an admin can list transactions', function () {
    $transaction = ExpenseTransaction::factory()->create();

    $this->actingAs(adminUser())
        ->getJson('/api/v1/admin/transactions')
        ->assertOk()
        ->assertJsonPath('meta.total', 1)
        ->assertJsonPath('data.0.id', $transaction->id);
});
