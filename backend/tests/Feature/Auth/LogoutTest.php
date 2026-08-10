<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;

use function Pest\Laravel\getJson;
use function Pest\Laravel\postJson;
use function Pest\Laravel\withHeader;

test('an authenticated user can logout through the API', function () {
    User::factory()->create(['email' => 'logout@example.com', 'password' => 'password']);

    withHeader('Origin', 'http://localhost');

    postJson('/api/v1/auth/login', [
        'email' => 'logout@example.com',
        'password' => 'password',
    ])->assertOk();

    postJson('/api/v1/auth/logout')
        ->assertOk()
        ->assertJsonPath('message', 'Logout successful');

    Auth::forgetGuards();

    getJson('/api/v1/auth/me')->assertUnauthorized();
});

test('a guest cannot logout through the API', function () {
    postJson('/api/v1/auth/logout')
        ->assertUnauthorized()
        ->assertJsonPath('message', 'Unauthenticated');
});
