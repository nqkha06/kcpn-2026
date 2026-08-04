<?php

test('an admin can view appearance settings', function () {
    $this->actingAs(adminUser())
        ->getJson('/api/v1/admin/appearance')
        ->assertOk()
        ->assertJsonStructure(['data' => ['languages', 'logos', 'general']]);
});
