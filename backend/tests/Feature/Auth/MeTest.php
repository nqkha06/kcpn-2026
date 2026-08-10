<?php

use function Pest\Laravel\actingAs;
use function Pest\Laravel\getJson;

test('an authenticated user can retrieve their account', function () {
    $user = regularUser();

    actingAs($user)
        ->getJson('/api/v1/auth/me')
        ->assertOk()
        ->assertJsonPath('data.user.email', $user->email)
        ->assertJsonPath('data.user.roles.0', 'user')
        ->assertJsonMissingPath('data.user.password')
        ->assertJsonMissingPath('data.user.remember_token')
        ->assertJsonMissingPath('data.user.two_factor_secret')
        ->assertJsonMissingPath('data.user.two_factor_recovery_codes');
});

test('the account endpoint rejects guests', function () {
    getJson('/api/v1/auth/me')->assertUnauthorized();
});
