<?php

use App\Models\Category;

test('a user can delete their unused private category', function () {
    $user = regularUser();
    $category = Category::factory()->create(['user_id' => $user->id]);

    $this->actingAs($user)
        ->deleteJson("/api/v1/user/categories/{$category->id}")
        ->assertOk();

    $this->assertDatabaseMissing('categories', ['id' => $category->id]);
});
