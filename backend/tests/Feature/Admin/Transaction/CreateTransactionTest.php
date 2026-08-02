<?php

use App\Models\Category;
use App\Models\UserWallet;

test('an admin can create a transaction', function () {
    $user = regularUser();
    $wallet = UserWallet::factory()->for($user)->create();
    $category = Category::factory()->create();

    $this->actingAs(adminUser())
        ->postJson('/api/v1/admin/transactions', [
            'user_id' => $user->id,
            'wallet_id' => $wallet->id,
            'category_id' => $category->id,
            'type' => 'expense',
            'amount' => 150000,
            'transacted_at' => '2026-07-29',
            'status' => 'posted',
            'note' => 'Admin created',
            'labels' => ['office'],
        ])
        ->assertCreated()
        ->assertJsonPath('data.user_id', $user->id);

    $this->assertDatabaseHas('expense_transactions', ['user_id' => $user->id, 'amount' => 150000]);
});
