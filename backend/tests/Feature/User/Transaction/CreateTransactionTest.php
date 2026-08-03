<?php

use App\Models\Category;
use App\Models\UserWallet;

test('a user can create a transaction', function () {
    $user = regularUser();
    $wallet = UserWallet::factory()->for($user)->create();
    $category = Category::factory()->create();

    $this->actingAs($user)
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

    $this->assertDatabaseHas('expense_transactions', ['user_id' => $user->id, 'amount' => 50000]);
});
