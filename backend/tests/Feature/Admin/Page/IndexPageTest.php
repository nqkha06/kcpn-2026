<?php

use App\Models\Page;

test('an admin can list pages', function () {
    Page::query()->create(['title' => 'API Page', 'slug' => 'api-page', 'status' => 'published']);

    $this->actingAs(adminUser())
        ->getJson('/api/v1/admin/pages')
        ->assertOk()
        ->assertJsonPath('meta.total', 1)
        ->assertJsonPath('data.0.slug', 'api-page');
});
