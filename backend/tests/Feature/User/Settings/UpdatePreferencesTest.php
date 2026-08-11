<?php

use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;
use function Pest\Laravel\patchJson;

test('a user can update their profile', function () {
    $user = User::factory()->create();
    $user->assignRole(\Spatie\Permission\Models\Role::firstOrCreate(['name' => 'user', 'guard_name' => 'web']));

    $response = actingAs($user, 'sanctum')->patchJson('/api/v1/user/settings/profile', [
        'name' => 'Jane Smith',
        'email' => 'jane@example.com',
    ]);

    $response->assertStatus(200);

    assertDatabaseHas('users', [
        'id' => $user->id,
        'name' => 'Jane Smith',
        'email' => 'jane@example.com',
    ]);
});

test('profile update validates the name', function () {
    $user = User::factory()->create();
    $user->assignRole(\Spatie\Permission\Models\Role::firstOrCreate(['name' => 'user', 'guard_name' => 'web']));

    $response = actingAs($user, 'sanctum')->patchJson('/api/v1/user/settings/profile', [
        'name' => '',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['name']);
});

test('a guest cannot update a profile', function () {
    patchJson('/api/v1/user/settings/profile', [
        'name' => 'Guest User',
        'email' => 'guest@example.com',
    ])->assertUnauthorized();

    assertDatabaseMissing('users', ['email' => 'guest@example.com']);
});

test('profile update validates required fields', function () {
    actingAs(regularUser())
        ->patchJson('/api/v1/user/settings/profile', [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['name', 'email']);
});

test('a user cannot update their profile to an existing email', function () {
    $user = regularUser();
    User::factory()->create(['email' => 'existing@example.com']);

    actingAs($user)
        ->patchJson('/api/v1/user/settings/profile', [
            'name' => $user->name,
            'email' => 'existing@example.com',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('email');

    expect($user->fresh()->email)->toBe($user->email);
});

test('changing an email clears the verification timestamp', function () {
    $user = regularUser();

    actingAs($user)
        ->patchJson('/api/v1/user/settings/profile', [
            'name' => $user->name,
            'email' => 'changed@example.com',
        ])
        ->assertOk()
        ->assertJsonPath('data.email_verified_at', null);

    expect($user->fresh()->email_verified_at)->toBeNull();
});

test('keeping the same email preserves the verification timestamp', function () {
    $user = regularUser();
    $verifiedAt = $user->email_verified_at;

    actingAs($user)
        ->patchJson('/api/v1/user/settings/profile', [
            'name' => 'Updated Name',
            'email' => $user->email,
        ])
        ->assertOk();

    expect($user->fresh()->email_verified_at?->equalTo($verifiedAt))->toBeTrue();
});

test('profile update response does not expose sensitive fields', function () {
    $user = regularUser();

    actingAs($user)
        ->patchJson('/api/v1/user/settings/profile', [
            'name' => 'Safe Response',
            'email' => $user->email,
        ])
        ->assertOk()
        ->assertJsonMissingPath('data.password')
        ->assertJsonMissingPath('data.remember_token')
        ->assertJsonMissingPath('data.two_factor_secret')
        ->assertJsonMissingPath('data.two_factor_recovery_codes');
});
