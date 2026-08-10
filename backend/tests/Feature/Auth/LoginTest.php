<?php

use App\Models\User;

use function Pest\Laravel\assertAuthenticatedAs;
use function Pest\Laravel\assertGuest;
use function Pest\Laravel\getJson;
use function Pest\Laravel\postJson;

test('a user can login through the API', function () {
    $user = User::factory()->create(['email' => 'login@example.com', 'password' => 'password']);

    postJson('/api/v1/auth/login', [
        'email' => 'login@example.com',
        'password' => 'password',
    ])
        ->assertOk()
        ->assertJsonPath('data.requires_two_factor', false)
        ->assertJsonPath('data.user.id', $user->id);

    assertAuthenticatedAs($user);

    getJson('/api/v1/auth/me')
        ->assertOk()
        ->assertJsonPath('data.user.id', $user->id);
});

test('login rejects invalid credentials', function () {
    User::factory()->create(['email' => 'login@example.com', 'password' => 'password']);

    postJson('/api/v1/auth/login', [
        'email' => 'login@example.com',
        'password' => 'incorrect-password',
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('email');
});

test('login rejects an unknown email without authenticating a user', function () {
    postJson('/api/v1/auth/login', [
        'email' => 'missing@example.com',
        'password' => 'password',
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('email');

    assertGuest();
});

test('login validates the required credentials', function () {
    postJson('/api/v1/auth/login', [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['email', 'password']);
});

test('login validates the email and remember formats', function () {
    postJson('/api/v1/auth/login', [
        'email' => 'not-an-email',
        'password' => 'password',
        'remember' => 'sometimes',
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['email', 'remember']);
});

test('login response does not expose authentication secrets', function () {
    User::factory()->withTwoFactor()->create([
        'email' => 'secure@example.com',
        'password' => 'password',
        'two_factor_confirmed_at' => null,
    ]);

    postJson('/api/v1/auth/login', [
        'email' => 'secure@example.com',
        'password' => 'password',
    ])
        ->assertOk()
        ->assertJsonMissingPath('data.user.password')
        ->assertJsonMissingPath('data.user.remember_token')
        ->assertJsonMissingPath('data.user.two_factor_secret')
        ->assertJsonMissingPath('data.user.two_factor_recovery_codes');
});

test('login is rate limited after repeated failed attempts', function () {
    User::factory()->create([
        'email' => 'limited@example.com',
        'password' => 'password',
    ]);

    foreach (range(1, 5) as $attempt) {
        postJson('/api/v1/auth/login', [
            'email' => 'limited@example.com',
            'password' => 'incorrect-password',
        ])->assertUnprocessable();
    }

    postJson('/api/v1/auth/login', [
        'email' => 'limited@example.com',
        'password' => 'incorrect-password',
    ])
        ->assertStatus(429)
        ->assertJsonValidationErrors('email');
});
