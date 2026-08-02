<?php

use App\Models\UserWallet;

test('a user can list only their wallets', function () {
    $user = regularUser();
    $wallet = UserWallet::factory()->for($user)->create();
    UserWallet::factory()->for(regularUser())->create();

    $this->actingAs($user)
        ->getJson('/api/v1/user/wallets')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $wallet->id);
});
