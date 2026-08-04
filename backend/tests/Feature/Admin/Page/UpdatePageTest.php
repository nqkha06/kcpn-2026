<?php

use App\Models\Page;

test('an admin can update a page', function () {
    $page = Page::query()->create(['title' => 'Old Page', 'slug' => 'old-page', 'status' => 'published']);

    $this->actingAs(adminUser())
        ->patchJson("/api/v1/admin/pages/{$page->id}", [
            'title' => 'Updated Page',
            'slug' => 'updated-page',
            'tags' => ['api', 'updated'],
            'status' => 'draft',
        ])
        ->assertOk()
        ->assertJsonPath('data.slug', 'updated-page')
        ->assertJsonPath('data.status', 'draft');

    $this->assertDatabaseHas('pages', ['id' => $page->id, 'title' => 'Updated Page']);
});
