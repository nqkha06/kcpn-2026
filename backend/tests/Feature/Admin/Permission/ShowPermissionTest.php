<?php

use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->user->assignRole(Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']));
});

it('can show a permission', function () {
    $permission = Permission::firstOrCreate(['name' => 'edit articles', 'guard_name' => 'web']);

    $response = $this->actingAs($this->user, 'sanctum')->getJson("/api/v1/admin/permissions/{$permission->id}");

    $response->assertStatus(200)
        ->assertJson([
            'data' => [
                'id' => $permission->id,
                'name' => 'edit articles',
            ],
        ]);
});
