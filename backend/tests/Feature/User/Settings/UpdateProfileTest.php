<?php

use App\Models\User;

it('can update profile and validations and databases', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user, 'sanctum')->patchJson('/api/v1/user/settings/profile', [
        'first_name' => 'Jane',
        'last_name' => 'Smith',
        'phone_number' => '1234567890',
    ]);

    $response->assertStatus(200);

    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'first_name' => 'Jane',
        'last_name' => 'Smith',
        'phone_number' => '1234567890',
    ]);
});

it('validates profile update fields', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user, 'sanctum')->patchJson('/api/v1/user/settings/profile', [
        'first_name' => '',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['first_name']);
});
