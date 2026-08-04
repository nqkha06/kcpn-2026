<?php

use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->user->assignRole(Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']));
});

it('can update a permission', function () {
    $permission = Permission::firstOrCreate(['name' => 'manage users', 'guard_name' => 'web']);

    $response = $this->actingAs($this->user, 'sanctum')->patchJson("/api/v1/admin/permissions/{$permission->id}", [
        'name' => 'manage staff',
    ]);

    $response->assertStatus(200);

    $this->assertDatabaseHas('permissions', [
        'id' => $permission->id,
        'name' => 'manage staff',
    ]);
});

it('validates unique permission name on update', function () {
    $permission1 = Permission::firstOrCreate(['name' => 'manage users', 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => 'manage staff', 'guard_name' => 'web']);

    $response = $this->actingAs($this->user, 'sanctum')->patchJson("/api/v1/admin/permissions/{$permission1->id}", [
        'name' => 'manage staff',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['name']);
});
