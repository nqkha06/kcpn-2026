<?php

use App\Models\Page;

test('an admin can view a page', function () {
    $page = Page::query()->create(['title' => 'API Page', 'slug' => 'api-page', 'status' => 'published']);

    $this->actingAs(adminUser())
        ->getJson("/api/v1/admin/pages/{$page->id}")
        ->assertOk()
        ->assertJsonPath('data.id', $page->id)
        ->assertJsonPath('data.slug', 'api-page');
});
