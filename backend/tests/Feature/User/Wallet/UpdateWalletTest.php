<?php

use App\Models\UserWallet;

test('a user can update their wallet', function () {
    $user = regularUser();
    $wallet = UserWallet::factory()->for($user)->create();

    $this->actingAs($user)
        ->patchJson("/api/v1/user/wallets/{$wallet->id}", [
            'name' => 'Ví API Updated',
            'currency' => 'USD',
            'opening_balance' => 750000,
            'is_default' => true,
        ])
        ->assertOk()
        ->assertJsonPath('data.name', 'Ví API Updated')
        ->assertJsonPath('data.currency', 'USD');

    $this->assertDatabaseHas('user_wallets', ['id' => $wallet->id, 'name' => 'Ví API Updated']);
});
