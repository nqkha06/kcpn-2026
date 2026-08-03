<?php

use App\Models\User;

test('an admin can update a user', function () {
    $user = User::factory()->create();

    $this->actingAs(adminUser())
        ->patchJson("/api/v1/admin/users/{$user->id}", [
            'name' => 'Updated User',
            'email' => 'updated@example.com',
            'roles' => [],
        ])
        ->assertOk()
        ->assertJsonPath('data.name', 'Updated User');

    $this->assertDatabaseHas('users', ['id' => $user->id, 'email' => 'updated@example.com']);
});
