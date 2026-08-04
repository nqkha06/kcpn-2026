<?php

use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->user->assignRole(Role::create(['name' => 'admin', 'guard_name' => 'web']));
});

it('can update a role and sync permissions', function () {
    $role = Role::create(['name' => 'manager', 'guard_name' => 'web']);
    $permission1 = Permission::create(['name' => 'manage users', 'guard_name' => 'web']);
    $permission2 = Permission::create(['name' => 'manage roles', 'guard_name' => 'web']);

    $role->givePermissionTo($permission1);

    $response = $this->actingAs($this->user, 'sanctum')->putJson("/api/v1/admin/roles/{$role->id}", [
        'name' => 'senior manager',
        'permissions' => [$permission2->id],
    ]);

    $response->assertStatus(200);

    $this->assertDatabaseHas('roles', [
        'id' => $role->id,
        'name' => 'senior manager',
    ]);

    expect($role->fresh()->hasPermissionTo($permission2))->toBeTrue();
    expect($role->fresh()->hasPermissionTo($permission1))->toBeFalse();
});
