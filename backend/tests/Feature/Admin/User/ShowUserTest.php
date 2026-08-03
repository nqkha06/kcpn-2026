<?php

use App\Models\User;

test('an admin can view a user', function () {
    $user = User::factory()->create();

    $this->actingAs(adminUser())
        ->getJson("/api/v1/admin/users/{$user->id}")
        ->assertOk()
        ->assertJsonPath('data.id', $user->id)
        ->assertJsonPath('data.email', $user->email);
});
