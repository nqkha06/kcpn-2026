<?php

use App\Models\User;

it('can update profile and validations and databases', function () {
    $user = User::factory()->create();
    $user->assignRole(\Spatie\Permission\Models\Role::firstOrCreate(['name' => 'user', 'guard_name' => 'web']));

    $response = $this->actingAs($user, 'sanctum')->patchJson('/api/v1/user/settings/profile', [
        'name' => 'Jane Smith',
        'email' => 'jane@example.com',
    ]);

    $response->assertStatus(200);

    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'name' => 'Jane Smith',
        'email' => 'jane@example.com',
    ]);
});

it('validates profile update fields', function () {
    $user = User::factory()->create();
    $user->assignRole(\Spatie\Permission\Models\Role::firstOrCreate(['name' => 'user', 'guard_name' => 'web']));

    $response = $this->actingAs($user, 'sanctum')->patchJson('/api/v1/user/settings/profile', [
        'name' => '',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['name']);
});
