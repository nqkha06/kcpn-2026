<?php

use App\Models\UserWallet;

test('a user can delete their wallet', function () {
    $user = regularUser();
    $wallet = UserWallet::factory()->for($user)->create();

    $this->actingAs($user)
        ->deleteJson("/api/v1/user/wallets/{$wallet->id}")
        ->assertOk();

    expect(UserWallet::withTrashed()->find($wallet->id)?->trashed())->toBeTrue();
});
