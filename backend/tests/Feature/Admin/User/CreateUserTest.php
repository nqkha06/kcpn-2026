<?php

test('an admin can create a user', function () {
    $this->actingAs(adminUser())
        ->postJson('/api/v1/admin/users', [
            'name' => 'API Managed User',
            'email' => 'managed@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'roles' => [],
        ])
        ->assertCreated()
        ->assertJsonPath('data.email', 'managed@example.com');

    $this->assertDatabaseHas('users', ['email' => 'managed@example.com']);
});
