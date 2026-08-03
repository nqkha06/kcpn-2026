<?php

use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->user->assignRole(Role::create(['name' => 'admin', 'guard_name' => 'web']));
});

it('can show a role and its permissions', function () {
    $role = Role::create(['name' => 'editor', 'guard_name' => 'web']);
    $permission = Permission::create(['name' => 'edit articles', 'guard_name' => 'web']);
    $role->givePermissionTo($permission);

    $response = $this->actingAs($this->user, 'sanctum')->getJson("/api/v1/admin/roles/{$role->id}");

    $response->assertStatus(200)
        ->assertJson([
            'data' => [
                'id' => $role->id,
                'name' => 'editor',
                'permissions' => [
                    [
                        'id' => $permission->id,
                        'name' => 'edit articles',
                    ],
                ],
            ],
        ]);
});
