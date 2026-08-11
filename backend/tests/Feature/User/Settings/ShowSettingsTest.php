<?php

use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\getJson;

test('settings return the current users profile and preferences', function () {
    $user = User::factory()->create([
        'name' => 'John Doe',
        'email' => 'john@example.com',
    ]);

    $user->assignRole(\Spatie\Permission\Models\Role::firstOrCreate(['name' => 'user', 'guard_name' => 'web']));

    $user->setMeta('currency', 'USD');

    $response = actingAs($user, 'sanctum')->getJson('/api/v1/user/settings');

    $response->assertStatus(200)
        ->assertJson([
            'data' => [
                'profile' => [
                    'name' => 'John Doe',
                    'email' => 'john@example.com',
                ],
                'preferences' => [
                    'currency' => 'USD',
                ],
            ],
        ]);
});

test('a guest cannot view settings', function () {
    $response = getJson('/api/v1/user/settings');
    $response->assertStatus(401);
});

test('settings use VND when the user has no currency preference', function () {
    $user = regularUser();

    actingAs($user)
        ->getJson('/api/v1/user/settings')
        ->assertOk()
        ->assertJsonPath('data.preferences.currency', 'VND');
});

test('an admin cannot access user only settings', function () {
    actingAs(adminUser())
        ->getJson('/api/v1/user/settings')
        ->assertForbidden();
});

test('settings response does not expose sensitive user fields', function () {
    actingAs(regularUser())
        ->getJson('/api/v1/user/settings')
        ->assertOk()
        ->assertJsonMissingPath('data.password')
        ->assertJsonMissingPath('data.remember_token')
        ->assertJsonMissingPath('data.two_factor_secret');
});
