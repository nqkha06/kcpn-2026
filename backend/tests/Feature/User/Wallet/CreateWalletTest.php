<?php

test('a user can create a wallet', function () {
    $user = regularUser();

    $this->actingAs($user)
        ->postJson('/api/v1/user/wallets', [
            'name' => 'Ví API',
            'currency' => 'vnd',
            'opening_balance' => 500000,
            'is_default' => false,
        ])
        ->assertCreated()
        ->assertJsonPath('data.currency', 'VND')
        ->assertJsonPath('data.is_default', true);

    $this->assertDatabaseHas('user_wallets', ['user_id' => $user->id, 'name' => 'Ví API']);
});
