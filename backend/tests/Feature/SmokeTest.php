<?php

use function Pest\Laravel\getJson;

test('the public API is reachable', function () {
    getJson('/api/v1/public/configuration')
        ->assertOk()
        ->assertJsonPath('success', true);
});
