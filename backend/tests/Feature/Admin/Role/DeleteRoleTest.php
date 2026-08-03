<?php

use App\Models\User;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->user->assignRole(Role::create(['name' => 'admin', 'guard_name' => 'web']));
});

it('can delete a role', function () {
    $role = Role::create(['name' => 'manager', 'guard_name' => 'web']);

    $response = $this->actingAs($this->user, 'sanctum')->deleteJson("/api/v1/admin/roles/{$role->id}");

    $response->assertStatus(200);

    $this->assertDatabaseMissing('roles', [
        'id' => $role->id,
    ]);
});

it('cannot delete system roles', function () {
    $role = Role::where('name', 'super-admin')->first();

    $response = $this->actingAs($this->user, 'sanctum')->deleteJson("/api/v1/admin/roles/{$role->id}");

    $response->assertStatus(403);

    $this->assertDatabaseHas('roles', [
        'id' => $role->id,
    ]);
});
