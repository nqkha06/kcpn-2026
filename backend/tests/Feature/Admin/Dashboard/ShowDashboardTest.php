<?php

use App\Models\ExpenseTransaction;

test('an admin can view dashboard aggregates', function () {
    $admin = adminUser();
    $user = regularUser();
    ExpenseTransaction::factory()->forUser($user)->income()->posted()->create();

    $this->actingAs($admin)
        ->getJson('/api/v1/admin/dashboard')
        ->assertOk()
        ->assertJsonPath('data.stats.users', 2)
        ->assertJsonCount(6, 'data.monthlyFlow')
        ->assertJsonStructure(['data' => ['stats', 'monthlyFlow', 'topExpenseCategories', 'recentTransactions']]);
});
