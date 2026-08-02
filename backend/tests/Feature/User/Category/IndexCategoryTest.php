<?php

use App\Models\Category;

test('a user can list visible categories', function () {
    $user = regularUser();
    $otherUser = regularUser();
    $global = Category::factory()->create(['name' => 'Global Category']);
    $private = Category::factory()->create(['user_id' => $user->id, 'name' => 'Private Category']);
    Category::factory()->create(['user_id' => $otherUser->id, 'name' => 'Hidden Category']);

    $this->actingAs($user)
        ->getJson('/api/v1/user/categories')
        ->assertOk()
        ->assertJsonCount(2, 'data')
        ->assertJsonFragment(['id' => $global->id])
        ->assertJsonFragment(['id' => $private->id])
        ->assertJsonMissing(['name' => 'Hidden Category']);
});
