<?php

use App\Models\ExpenseTransaction;

test('a user can list only their transactions', function () {
    $user = regularUser();
    $otherUser = regularUser();
    $visible = ExpenseTransaction::factory()->forUser($user)->create();
    ExpenseTransaction::factory()->forUser($otherUser)->create();

    $this->actingAs($user)
        ->getJson('/api/v1/user/transactions')
        ->assertOk()
        ->assertJsonPath('meta.total', 1)
        ->assertJsonPath('data.0.id', $visible->id);
});
