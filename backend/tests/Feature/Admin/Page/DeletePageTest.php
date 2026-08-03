<?php

use App\Models\Page;

test('an admin can delete a page', function () {
    $page = Page::query()->create(['title' => 'API Page', 'slug' => 'api-page', 'status' => 'draft']);

    $this->actingAs(adminUser())
        ->deleteJson("/api/v1/admin/pages/{$page->id}")
        ->assertOk();

    $this->assertDatabaseMissing('pages', ['id' => $page->id]);
});
