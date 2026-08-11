<?php

use function Pest\Laravel\actingAs;

test('can view user settings', function () {
    actingAs(regularUser())
        ->getJson(route('api.v1.user.settings.show'))
        ->assertOk()
        ->assertJsonStructure(['data' => ['profile', 'preferences', 'currency_options']]);
});

test('can update user profile', function () {
    $user = regularUser();

    actingAs($user)
        ->patchJson(route('api.v1.user.settings.profile.update'), [
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
        ])
        ->assertOk()
        ->assertJsonPath('data.name', 'Updated Name');

    expect($user->fresh()->name)->toBe('Updated Name');
});

test('can update user preferences', function () {
    actingAs(regularUser())
        ->patchJson(route('api.v1.user.settings.preferences.update'), [
            'currency' => 'VND',
        ])
        ->assertOk();
});
