<?php

use App\Models\Page;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\patchJson;

test('an admin can update a page', function () {
    $page = Page::query()->create(['title' => 'Old Page', 'slug' => 'old-page', 'status' => 'published']);

    actingAs(adminUser())
        ->patchJson("/api/v1/admin/pages/{$page->id}", [
            'title' => 'Updated Page',
            'slug' => 'updated-page',
            'tags' => ['api', 'updated'],
            'status' => 'draft',
        ])
        ->assertOk()
        ->assertJsonPath('data.slug', 'updated-page')
        ->assertJsonPath('data.status', 'draft');

    assertDatabaseHas('pages', ['id' => $page->id, 'title' => 'Updated Page']);
});

test('a guest cannot update an admin page', function () {
    $page = Page::query()->create(['title' => 'Page', 'slug' => 'page', 'status' => 'draft']);

    patchJson("/api/v1/admin/pages/{$page->id}", [
        'title' => 'Guest Update',
        'status' => 'draft',
    ])->assertUnauthorized();
});

test('a regular user cannot update an admin page', function () {
    $page = Page::query()->create(['title' => 'Page', 'slug' => 'page', 'status' => 'draft']);

    actingAs(regularUser())
        ->patchJson("/api/v1/admin/pages/{$page->id}", [
            'title' => 'Unauthorized Update',
            'status' => 'draft',
        ])
        ->assertForbidden();
});

test('page update validates required fields', function () {
    $page = Page::query()->create(['title' => 'Page', 'slug' => 'page', 'status' => 'draft']);

    actingAs(adminUser())
        ->patchJson("/api/v1/admin/pages/{$page->id}", [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['title', 'status']);
});

test('a page can keep its own slug when updated', function () {
    $page = Page::query()->create(['title' => 'Page', 'slug' => 'page', 'status' => 'draft']);

    actingAs(adminUser())
        ->patchJson("/api/v1/admin/pages/{$page->id}", [
            'title' => 'Updated Title',
            'slug' => 'page',
            'status' => 'published',
        ])
        ->assertOk()
        ->assertJsonPath('data.slug', 'page');
});

test('page update rejects another pages slug', function () {
    Page::query()->create(['title' => 'Existing', 'slug' => 'existing', 'status' => 'draft']);
    $page = Page::query()->create(['title' => 'Page', 'slug' => 'page', 'status' => 'draft']);

    actingAs(adminUser())
        ->patchJson("/api/v1/admin/pages/{$page->id}", [
            'title' => 'Page',
            'slug' => 'existing',
            'status' => 'draft',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('slug');

    expect($page->fresh()->slug)->toBe('page');
});

test('updating a missing admin page returns not found', function () {
    actingAs(adminUser())
        ->patchJson('/api/v1/admin/pages/999999', [
            'title' => 'Missing',
            'status' => 'draft',
        ])
        ->assertNotFound();
});
