<?php

use App\Models\Category;

test('an admin can update a category', function () {
    $category = Category::factory()->create();

    $this->actingAs(adminUser())
        ->patchJson("/api/v1/admin/categories/{$category->id}", [
            'name' => 'Updated Category',
            'color' => '#3B82F6',
            'description' => 'Updated',
            'status' => 'inactive',
        ])
        ->assertOk()
        ->assertJsonPath('data.status', 'inactive');

    $this->assertDatabaseHas('categories', ['id' => $category->id, 'name' => 'Updated Category']);
});
