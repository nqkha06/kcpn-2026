<?php

use App\Models\User;

it('returns the settings of the current user', function () {
    $user = User::factory()->create([
        'first_name' => 'John',
        'last_name' => 'Doe',
    ]);

    $user->setMeta('currency', 'USD');

    $response = $this->actingAs($user, 'sanctum')->getJson('/api/v1/user/settings');

    $response->assertStatus(200)
        ->assertJson([
            'data' => [
                'profile' => [
                    'first_name' => 'John',
                    'last_name' => 'Doe',
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
