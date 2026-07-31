<?php

test('the public API is reachable', function () {
    $this->getJson('/api/v1/public/configuration')
        ->assertOk()
        ->assertJsonPath('success', true);
});
