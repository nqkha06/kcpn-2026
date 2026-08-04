<?php

use App\Models\User;

it('can save the currency to user_metas', function () {
    $user = User::factory()->create();
    $user->assignRole(\Spatie\Permission\Models\Role::firstOrCreate(['name' => 'user', 'guard_name' => 'web']));

    $response = $this->actingAs($user, 'sanctum')->patchJson('/api/v1/user/settings/preferences', [
        'currency' => 'EUR',
    ]);

    $response->assertStatus(200);

    $this->assertDatabaseHas('user_metas', [
        'user_id' => $user->id,
        'meta_key' => 'currency',
        'meta_value' => 'EUR',
    ]);

    expect($user->getMeta('currency'))->toBe('EUR');
});
