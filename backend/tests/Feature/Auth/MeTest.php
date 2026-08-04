<?php

test('an authenticated user can retrieve their account', function () {
    $user = regularUser();

    $this->actingAs($user)
        ->getJson('/api/v1/auth/me')
        ->assertOk()
        ->assertJsonPath('data.user.email', $user->email);
});

test('the account endpoint rejects guests', function () {
    $this->getJson('/api/v1/auth/me')->assertUnauthorized();
});
