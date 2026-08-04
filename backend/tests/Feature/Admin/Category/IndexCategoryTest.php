<?php

use App\Models\Category;

test('an admin can list categories', function () {
    $category = Category::factory()->create();

    $this->actingAs(adminUser())
        ->getJson('/api/v1/admin/categories')
        ->assertOk()
        ->assertJsonPath('meta.total', 1)
        ->assertJsonPath('data.0.id', $category->id);
});
