<?php

use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->user->assignRole(Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']));
});

it('can list permissions', function () {
    Permission::firstOrCreate(['name' => 'edit articles', 'guard_name' => 'web']);

    $response = $this->actingAs($this->user, 'sanctum')->getJson('/api/v1/admin/permissions');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                '*' => ['id', 'name', 'created_at'],
            ],
        ]);
});

it('unauthorized user cannot list permissions', function () {
    $response = $this->getJson('/api/v1/admin/permissions');
    $response->assertStatus(401);
});
