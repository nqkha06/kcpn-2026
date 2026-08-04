<?php

use App\Models\User;

test('a user can login through the API', function () {
    $user = User::factory()->create(['email' => 'login@example.com', 'password' => 'password']);

    $this->postJson('/api/v1/auth/login', [
        'email' => 'login@example.com',
        'password' => 'password',
    ])
        ->assertOk()
        ->assertJsonPath('data.requires_two_factor', false)
        ->assertJsonPath('data.user.id', $user->id);
});

test('login rejects invalid credentials', function () {
    User::factory()->create(['email' => 'login@example.com', 'password' => 'password']);

    $this->postJson('/api/v1/auth/login', [
        'email' => 'login@example.com',
        'password' => 'incorrect-password',
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('email');
});
