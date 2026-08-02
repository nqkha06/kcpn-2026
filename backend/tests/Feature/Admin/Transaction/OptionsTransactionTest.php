<?php

use App\Models\Category;
use App\Models\UserWallet;

test('an admin can get transaction options', function () {
    $user = regularUser();
    $wallet = UserWallet::factory()->for($user)->create();
    $category = Category::factory()->create();

    $this->actingAs(adminUser())
        ->getJson('/api/v1/admin/transactions/options')
        ->assertOk()
        ->assertJsonFragment(['id' => $wallet->id, 'user_id' => $user->id, 'name' => $wallet->name])
        ->assertJsonFragment(['id' => $category->id, 'name' => $category->name]);
});
