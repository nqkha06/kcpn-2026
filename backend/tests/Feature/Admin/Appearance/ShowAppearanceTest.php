<?php

use function Pest\Laravel\actingAs;
use function Pest\Laravel\getJson;

test('an admin can view appearance settings', function () {
    actingAs(adminUser())
        ->getJson('/api/v1/admin/appearance')
        ->assertOk()
        ->assertJsonStructure(['data' => ['languages', 'logos', 'general']]);
});

test('a guest cannot view appearance settings', function () {
    getJson('/api/v1/admin/appearance')->assertUnauthorized();
});

test('a regular user cannot view appearance settings', function () {
    actingAs(regularUser())
        ->getJson('/api/v1/admin/appearance')
        ->assertForbidden();
});

test('appearance settings expose configured languages and empty logo paths by default', function () {
    $response = actingAs(adminUser())
        ->getJson('/api/v1/admin/appearance')
        ->assertOk()
        ->assertJsonPath('data.languages.0.code', 'vi')
        ->assertJsonPath('data.logos.logo_light.path', null)
        ->assertJsonPath('data.logos.logo_light.url', null);

    $defaultLanguage = collect($response->json('data.languages'))->firstWhere('is_default', true);

    expect($defaultLanguage['code'])->toBe(strtolower((string) config('app.locale')));
});
