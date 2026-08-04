<?php

use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->user->assignRole(Role::create(['name' => 'admin', 'guard_name' => 'web']));
});

it('can create a role and assign permissions', function () {
    $permission = Permission::create(['name' => 'publish articles', 'guard_name' => 'web']);

    $response = $this->actingAs($this->user, 'sanctum')->postJson('/api/v1/admin/roles', [
        'name' => 'writer',
        'permissions' => [$permission->id],
    ]);

    $response->assertStatus(201);

    $this->assertDatabaseHas('roles', [
        'name' => 'writer',
        'guard_name' => 'web',
    ]);

    $role = Role::where('name', 'writer')->first();
    expect($role->hasPermissionTo('publish articles'))->toBeTrue();
});
