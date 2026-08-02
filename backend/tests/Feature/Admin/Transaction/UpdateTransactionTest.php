<?php

use App\Models\ExpenseTransaction;

test('an admin can update a transaction', function () {
    $transaction = ExpenseTransaction::factory()->create(['status' => 'posted']);

    $this->actingAs(adminUser())
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

    $this->assertDatabaseHas('expense_transactions', ['id' => $transaction->id, 'status' => 'pending']);
});
