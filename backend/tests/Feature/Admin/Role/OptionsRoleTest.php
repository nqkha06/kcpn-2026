<?php

use App\Models\User;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->user->assignRole(Role::create(['name' => 'admin', 'guard_name' => 'web']));
});

it('can get role options', function () {
    Role::create(['name' => 'manager', 'guard_name' => 'web']);
    Role::create(['name' => 'editor', 'guard_name' => 'web']);

    $response = $this->actingAs($this->user, 'sanctum')->getJson('/api/v1/admin/roles/options');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                '*' => ['id', 'name'],
            ],
        ]);
});
