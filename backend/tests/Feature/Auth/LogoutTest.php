<?php

use App\Models\User;

test('an authenticated user can logout through the API', function () {
    User::factory()->create(['email' => 'logout@example.com', 'password' => 'password']);

    $this->postJson('/api/v1/auth/login', [
        'email' => 'logout@example.com',
        'password' => 'password',
    ])->assertOk();

    $this->postJson('/api/v1/auth/logout')
        ->assertOk()
        ->assertJsonPath('message', 'Logout successful');
});
