<?php

use App\Models\User;

test('an admin can delete a user', function () {
    $user = User::factory()->create();

    $this->actingAs(adminUser())
        ->deleteJson("/api/v1/admin/users/{$user->id}")
        ->assertOk();

    $this->assertDatabaseMissing('users', ['id' => $user->id]);
});
