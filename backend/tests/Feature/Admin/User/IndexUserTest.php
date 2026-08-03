<?php

use App\Models\User;

test('an admin can list users', function () {
    $admin = adminUser();
    User::factory()->create(['email' => 'listed@example.com']);

    $this->actingAs($admin)
        ->getJson('/api/v1/admin/users')
        ->assertOk()
        ->assertJsonPath('meta.total', 2)
        ->assertJsonFragment(['email' => 'listed@example.com']);
});
