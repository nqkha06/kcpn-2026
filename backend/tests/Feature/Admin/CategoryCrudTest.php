<?php

use App\Models\Category;

test('admin can view category index', function () {
    $admin = adminUser();

    $this->actingAs($admin);
    $this->withoutVite();

    $this->get(route('admin.categories.index'))
        ->assertOk();
});

test('admin can create a category', function () {
    $admin = adminUser();

    $this->actingAs($admin);

    $payload = [
        'name' => 'Food',
        'color' => '#10B981',
        'description' => 'Food and dining expenses',
        'status' => 'active',
    ];

    $this->post(route('admin.categories.store'), $payload)
        ->assertRedirect(route('admin.categories.index'));

    $categoryExists = Category::query()
        ->where('name', 'Food')
        ->where('color', '#10B981')
        ->where('status', 'active')
        ->exists();

    expect($categoryExists)->toBeTrue();
});

test('admin can update a category', function () {
    $admin = adminUser();
    $category = Category::factory()->create();

    $this->actingAs($admin);

    $payload = [
        'name' => 'Transport',
        'color' => '#3B82F6',
        'description' => 'Commuting and travel',
        'status' => 'inactive',
    ];

    $this->put(route('admin.categories.update', $category), $payload)
        ->assertRedirect(route('admin.categories.index'));

    $updatedExists = Category::query()
        ->whereKey($category->id)
        ->where('name', 'Transport')
        ->where('color', '#3B82F6')
        ->where('status', 'inactive')
        ->exists();

    expect($updatedExists)->toBeTrue();
});

test('admin can delete a category', function () {
    $admin = adminUser();
    $category = Category::factory()->create();

    $this->actingAs($admin);

    $this->delete(route('admin.categories.destroy', $category))
        ->assertRedirect(route('admin.categories.index'));

    $categoryStillExists = Category::query()
        ->whereKey($category->id)
        ->exists();

    expect($categoryStillExists)->toBeFalse();
});
