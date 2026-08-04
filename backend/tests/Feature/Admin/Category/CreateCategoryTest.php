<?php

test('an admin can create a category', function () {
    $this->actingAs(adminUser())
        ->postJson('/api/v1/admin/categories', [
            'name' => 'API Category',
            'color' => '#10B981',
            'description' => 'Created through API tests',
            'status' => 'active',
        ])
        ->assertCreated()
        ->assertJsonPath('data.name', 'API Category');

    $this->assertDatabaseHas('categories', ['name' => 'API Category', 'user_id' => null]);
});
