<?php

use App\Models\Category;

test('a user can view their private category', function () {
    $user = regularUser();
    $category = Category::factory()->create(['user_id' => $user->id]);

    $this->actingAs($user)
        ->getJson("/api/v1/user/categories/{$category->id}")
        ->assertOk()
        ->assertJsonPath('data.id', $category->id);
});

test('a user cannot view another users private category', function () {
    $owner = regularUser();
    $category = Category::factory()->create(['user_id' => $owner->id]);

    $this->actingAs(regularUser())
        ->getJson("/api/v1/user/categories/{$category->id}")
        ->assertForbidden();
});
