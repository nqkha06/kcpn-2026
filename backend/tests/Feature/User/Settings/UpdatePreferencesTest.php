<?php

use function Pest\Laravel\actingAs;
use function Pest\Laravel\patchJson;

test('a user can update their currency preference', function () {
    $user = regularUser();

    $response = actingAs($user, 'sanctum')->patchJson('/api/v1/user/settings/preferences', [
        'currency' => 'USD',
    ]);

    $response->assertStatus(200)
        ->assertJsonPath('data.preferences.currency', 'USD');

    expect($user->fresh()->getMeta('currency'))->toBe('USD');
});

test('a guest cannot update preferences', function () {
    patchJson('/api/v1/user/settings/preferences', [
        'currency' => 'USD',
    ])->assertUnauthorized();
});

test('an admin cannot update preferences on this endpoint', function () {
    actingAs(adminUser())
        ->patchJson('/api/v1/user/settings/preferences', [
            'currency' => 'USD',
        ])
        ->assertForbidden();
});

test('preference update validates currency is required', function () {
    actingAs(regularUser())
        ->patchJson('/api/v1/user/settings/preferences', [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('currency');
});

test('preference update validates currency is exactly 3 characters', function () {
    actingAs(regularUser())
        ->patchJson('/api/v1/user/settings/preferences', [
            'currency' => 'US',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('currency');

    actingAs(regularUser())
        ->patchJson('/api/v1/user/settings/preferences', [
            'currency' => 'USDD',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('currency');
});

test('preference update validates currency is in the allowed list', function () {
    actingAs(regularUser())
        ->patchJson('/api/v1/user/settings/preferences', [
            'currency' => 'JPY',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('currency');
});
