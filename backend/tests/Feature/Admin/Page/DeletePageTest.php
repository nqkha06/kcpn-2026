<?php

use App\Models\Page;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;
use function Pest\Laravel\deleteJson;

test('an admin can delete a page', function () {
    $page = Page::query()->create(['title' => 'API Page', 'slug' => 'api-page', 'status' => 'draft']);

    actingAs(adminUser())
        ->deleteJson("/api/v1/admin/pages/{$page->id}")
        ->assertOk();

    assertDatabaseMissing('pages', ['id' => $page->id]);
});

test('a guest cannot delete an admin page', function () {
    $page = Page::query()->create(['title' => 'Page', 'slug' => 'page', 'status' => 'draft']);

    deleteJson("/api/v1/admin/pages/{$page->id}")->assertUnauthorized();

    assertDatabaseHas('pages', ['id' => $page->id]);
});

test('a regular user cannot delete an admin page', function () {
    $page = Page::query()->create(['title' => 'Page', 'slug' => 'page', 'status' => 'draft']);

    actingAs(regularUser())
        ->deleteJson("/api/v1/admin/pages/{$page->id}")
        ->assertForbidden();
});

test('deleting a missing admin page returns not found', function () {
    actingAs(adminUser())
        ->deleteJson('/api/v1/admin/pages/999999')
        ->assertNotFound();
});
