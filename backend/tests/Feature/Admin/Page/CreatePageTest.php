<?php

test('an admin can create a page', function () {
    $admin = adminUser();

    $this->actingAs($admin)
        ->postJson('/api/v1/admin/pages', [
            'title' => 'API Page',
            'content' => '<p>Created through API tests</p>',
            'tags' => 'api, test, api',
            'status' => 'published',
        ])
        ->assertCreated()
        ->assertJsonPath('data.slug', 'api-page')
        ->assertJsonPath('data.author.id', $admin->id)
        ->assertJsonPath('data.tags', ['api', 'test']);

    $this->assertDatabaseHas('pages', ['title' => 'API Page', 'slug' => 'api-page']);
});
