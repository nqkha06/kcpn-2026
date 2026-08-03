<?php

use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->user->assignRole(Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']));
});

it('can create a permission', function () {
    $response = $this->actingAs($this->user, 'sanctum')->postJson('/api/v1/admin/permissions', [
        'name' => 'publish articles',
    ]);

    $response->assertStatus(201);

    $this->assertDatabaseHas('permissions', [
        'name' => 'publish articles',
        'guard_name' => 'web',
    ]);
});

it('validates unique permission name', function () {
    Permission::firstOrCreate(['name' => 'publish articles', 'guard_name' => 'web']);

    $response = $this->actingAs($this->user, 'sanctum')->postJson('/api/v1/admin/permissions', [
        'name' => 'publish articles',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['name']);
});
