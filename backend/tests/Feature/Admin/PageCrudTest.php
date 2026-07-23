<?php

use App\Enums\BaseStatusEnum;
use App\Models\Category;
use App\Models\Page;

test('admin can view page index', function () {
    $admin = adminUser();

    $this->actingAs($admin);
    $this->withoutVite();

    $this->get(route('admin.pages.index'))
        ->assertOk();
});

test('admin can create a page', function () {
    $admin = adminUser();
    $category = Category::factory()->create();

    $this->actingAs($admin);

    $payload = [
        'title' => 'Getting Started',
        'slug' => 'getting-started',
        'content' => 'Welcome content',
        'meta_title' => 'Getting Started',
        'meta_description' => 'Page description',
        'meta_keywords' => 'start,guide',
        'category_id' => $category->id,
        'tags' => 'guide,starter',
        'status' => BaseStatusEnum::DRAFT->value,
    ];

    $this->post(route('admin.pages.store'), $payload)
        ->assertRedirect(route('admin.pages.index'));

    $pageExists = Page::query()
        ->where('title', 'Getting Started')
        ->where('slug', 'getting-started')
        ->where('status', BaseStatusEnum::DRAFT->value)
        ->exists();

    expect($pageExists)->toBeTrue();
});

test('admin can update a page', function () {
    $admin = adminUser();
    $page = Page::query()->create([
        'user_id' => $admin->id,
        'title' => 'Old Title',
        'slug' => 'old-title',
        'status' => BaseStatusEnum::DRAFT->value,
    ]);

    $this->actingAs($admin);

    $payload = [
        'title' => 'New Title',
        'slug' => 'new-title',
        'content' => 'Updated content',
        'meta_title' => 'New Meta Title',
        'meta_description' => 'Updated description',
        'meta_keywords' => 'new,updated',
        'category_id' => null,
        'tags' => ['updated', 'article'],
        'status' => BaseStatusEnum::PUBLISHED->value,
    ];

    $this->put(route('admin.pages.update', $page), $payload)
        ->assertRedirect(route('admin.pages.index'));

    $updatedExists = Page::query()
        ->whereKey($page->id)
        ->where('title', 'New Title')
        ->where('slug', 'new-title')
        ->where('status', BaseStatusEnum::PUBLISHED->value)
        ->exists();

    expect($updatedExists)->toBeTrue();
});

test('admin can delete a page', function () {
    $admin = adminUser();
    $page = Page::query()->create([
        'user_id' => $admin->id,
        'title' => 'Delete Me',
        'slug' => 'delete-me',
        'status' => BaseStatusEnum::DRAFT->value,
    ]);

    $this->actingAs($admin);

    $this->delete(route('admin.pages.destroy', $page))
        ->assertRedirect(route('admin.pages.index'));

    $pageStillExists = Page::query()
        ->whereKey($page->id)
        ->exists();

    expect($pageStillExists)->toBeFalse();
});
