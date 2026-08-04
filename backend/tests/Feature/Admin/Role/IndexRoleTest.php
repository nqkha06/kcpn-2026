<?php

use App\Models\User;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->user->assignRole(Role::create(['name' => 'admin', 'guard_name' => 'web']));
});

it('can list roles', function () {
    Role::create(['name' => 'editor', 'guard_name' => 'web']);

    $response = $this->actingAs($this->user, 'sanctum')->getJson('/api/v1/admin/roles');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                '*' => ['id', 'name', 'created_at'],
            ],
        ]);
});

it('unauthorized user cannot list roles', function () {
    $response = $this->getJson('/api/v1/admin/roles');
    $response->assertStatus(401);
});
