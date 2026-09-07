<?php

use function Pest\Laravel\get;
use function Pest\Laravel\getJson;

test('the public API is reachable', function () {
    getJson('/api/v1/public/configuration')
        ->assertOk()
        ->assertJsonPath('success', true);
});

test('legacy server rendered routes are not registered', function () {
    get('/')->assertNotFound();
    get('/login')->assertNotFound();
});
