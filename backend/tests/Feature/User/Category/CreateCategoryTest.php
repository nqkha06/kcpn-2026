<?php

test('a user can create a private category', function () {
    $user = regularUser();

    $this->actingAs($user)
        ->postJson('/api/v1/user/categories', [
            'name' => 'Private Category',
            'color' => '#10B981',
            'description' => 'Private',
        ])
        ->assertCreated()
        ->assertJsonPath('data.is_private', true);

    $this->assertDatabaseHas('categories', ['user_id' => $user->id, 'name' => 'Private Category']);
});
