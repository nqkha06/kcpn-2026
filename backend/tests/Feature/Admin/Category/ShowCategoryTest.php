<?php

use App\Models\Category;

test('an admin can view a category', function () {
    $category = Category::factory()->create();

    $this->actingAs(adminUser())
        ->getJson("/api/v1/admin/categories/{$category->id}")
        ->assertOk()
        ->assertJsonPath('data.id', $category->id);
});
