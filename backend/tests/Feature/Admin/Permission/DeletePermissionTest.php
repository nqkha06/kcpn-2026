<?php

use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->user->assignRole(Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']));
});

it('can delete a permission', function () {
    $permission = Permission::firstOrCreate(['name' => 'manage users', 'guard_name' => 'web']);

    $response = $this->actingAs($this->user, 'sanctum')->deleteJson("/api/v1/admin/permissions/{$permission->id}");

    $response->assertStatus(200);

    $this->assertDatabaseMissing('permissions', [
        'id' => $permission->id,
    ]);
});
