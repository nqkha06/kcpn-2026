<?php

use App\Models\Page;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\getJson;

test('an admin can view a page', function () {
    $page = Page::query()->create(['title' => 'API Page', 'slug' => 'api-page', 'status' => 'published']);

    actingAs(adminUser())
        ->getJson("/api/v1/admin/pages/{$page->id}")
        ->assertOk()
        ->assertJsonPath('data.id', $page->id)
        ->assertJsonPath('data.slug', 'api-page');
});

test('a guest cannot view an admin page', function () {
    $page = Page::query()->create(['title' => 'Page', 'slug' => 'page', 'status' => 'draft']);

    getJson("/api/v1/admin/pages/{$page->id}")->assertUnauthorized();
});

test('a regular user cannot view an admin page', function () {
    $page = Page::query()->create(['title' => 'Page', 'slug' => 'page', 'status' => 'draft']);

    actingAs(regularUser())
        ->getJson("/api/v1/admin/pages/{$page->id}")
        ->assertForbidden();
});

test('viewing a missing admin page returns not found', function () {
    actingAs(adminUser())
        ->getJson('/api/v1/admin/pages/999999')
        ->assertNotFound();
});
