<?php

use App\Models\Category;
use App\Models\UserWallet;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\getJson;

test('an admin can get transaction options', function () {
    $user = regularUser();
    $wallet = UserWallet::factory()->for($user)->create();
    $category = Category::factory()->create();

    actingAs(adminUser())
        ->getJson('/api/v1/admin/transactions/options')
        ->assertOk()
        ->assertJsonFragment(['id' => $wallet->id, 'user_id' => $user->id, 'name' => $wallet->name])
        ->assertJsonFragment(['id' => $category->id, 'name' => $category->name]);
});

test('a guest cannot get admin transaction options', function () {
    getJson('/api/v1/admin/transactions/options')->assertUnauthorized();
});

test('a regular user cannot get admin transaction options', function () {
    actingAs(regularUser())
        ->getJson('/api/v1/admin/transactions/options')
        ->assertForbidden();
});
