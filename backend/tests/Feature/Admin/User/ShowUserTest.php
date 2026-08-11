<?php

use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\getJson;

test('an admin can view a user', function () {
    $user = User::factory()->create();

    actingAs(adminUser())
        ->getJson("/api/v1/admin/users/{$user->id}")
        ->assertOk()
        ->assertJsonPath('data.id', $user->id)
        ->assertJsonPath('data.email', $user->email)
        ->assertJsonMissingPath('data.password')
        ->assertJsonMissingPath('data.remember_token')
        ->assertJsonMissingPath('data.two_factor_secret')
        ->assertJsonMissingPath('data.two_factor_recovery_codes');
});

test('a guest cannot view a user', function () {
    $user = User::factory()->create();

    getJson("/api/v1/admin/users/{$user->id}")
        ->assertUnauthorized();
});

test('a regular user cannot view a user', function () {
    $user = User::factory()->create();

    actingAs(regularUser())
        ->getJson("/api/v1/admin/users/{$user->id}")
        ->assertForbidden();
});

test('viewing a missing user returns not found', function () {
    actingAs(adminUser())
        ->getJson('/api/v1/admin/users/999999')
        ->assertNotFound();
});
