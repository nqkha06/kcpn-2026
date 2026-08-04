<?php

use App\Models\Category;

test('an admin can delete a category', function () {
    $category = Category::factory()->create();

    $this->actingAs(adminUser())
        ->deleteJson("/api/v1/admin/categories/{$category->id}")
        ->assertOk();

    $this->assertDatabaseMissing('categories', ['id' => $category->id]);
});
