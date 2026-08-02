<?php

use App\Models\Category;

test('a user can update their private category', function () {
    $user = regularUser();
    $category = Category::factory()->create(['user_id' => $user->id]);

    $this->actingAs($user)
        ->patchJson("/api/v1/user/categories/{$category->id}", [
            'name' => 'Updated Private Category',
            'color' => '#3B82F6',
            'description' => 'Updated',
        ])
        ->assertOk()
        ->assertJsonPath('data.name', 'Updated Private Category');

    $this->assertDatabaseHas('categories', ['id' => $category->id, 'name' => 'Updated Private Category']);
});
