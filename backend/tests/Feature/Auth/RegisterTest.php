<?php

test('a guest can register through the API', function () {
    $this->postJson('/api/v1/auth/register', [
        'name' => 'API User',
        'email' => 'api-user@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ])
        ->assertCreated()
        ->assertJsonPath('data.user.email', 'api-user@example.com')
        ->assertJsonPath('data.user.roles.0', 'user');

    $this->assertDatabaseHas('users', ['email' => 'api-user@example.com']);
});
