<?php

use App\Models\User;

it('returns the settings of the current user', function () {
    $user = User::factory()->create([
        'name' => 'John Doe',
        'email' => 'john@example.com',
    ]);
    
    $user->assignRole(\Spatie\Permission\Models\Role::firstOrCreate(['name' => 'user', 'guard_name' => 'web']));
    
    $user->setMeta('currency', 'USD');

    $response = $this->actingAs($user, 'sanctum')->getJson('/api/v1/user/settings');

    $response->assertStatus(200)
        ->assertJson([
            'data' => [
                'profile' => [
                    'name' => 'John Doe',
                    'email' => 'john@example.com',
                ],
                'preferences' => [
                    'currency' => 'USD',
                ],
            ],
        ]);
});

it('unauthorized user receives 401 on settings', function () {
    $response = $this->getJson('/api/v1/user/settings');
    $response->assertStatus(401);
});
