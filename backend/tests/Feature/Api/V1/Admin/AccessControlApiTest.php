<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

function accessControlApiActor(string $role): User
{
    $user = User::factory()->create();
    $user->assignRole(Role::findOrCreate($role, 'web'));

    return $user;
}

beforeEach(function (): void {
    $this->withHeaders([
        'Accept' => 'application/json',
        'Origin' => 'http://localhost:3000',
        'Referer' => 'http://localhost:3000/admin',
    ]);
});

test('access control routes reject guests and non admin users', function () {
    $this->getJson('/api/v1/admin/users')
        ->assertUnauthorized()
        ->assertJsonPath('success', false)
        ->assertJsonPath('message', 'Unauthenticated');

    $this->actingAs(accessControlApiActor('user'), 'web')
        ->getJson('/api/v1/admin/roles')
        ->assertForbidden()
        ->assertJsonPath('success', false)
        ->assertJsonPath('message', 'Forbidden');
});

test('admin can create filter show update and delete users with roles', function () {
    $admin = accessControlApiActor('admin');
    $userRole = Role::findOrCreate('user', 'web');
    $editorRole = Role::findOrCreate('editor', 'web');
    $this->actingAs($admin, 'web');

    $created = $this->postJson('/api/v1/admin/users', [
        'name' => 'Target Person',
        'email' => 'target@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'roles' => [$userRole->id],
    ])
        ->assertCreated()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.email', 'target@example.com')
        ->assertJsonPath('data.roles.0.name', 'user');

    $userId = $created->json('data.id');

    $this->getJson('/api/v1/admin/users?search=Target&email=target%40example.com&role=user&sort=name&direction=asc&per_page=1')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $userId)
        ->assertJsonPath('meta.total', 1)
        ->assertJsonPath('meta.per_page', 1);

    $this->getJson('/api/v1/admin/users/'.$userId)
        ->assertOk()
        ->assertJsonPath('data.name', 'Target Person');

    $this->patchJson('/api/v1/admin/users/'.$userId, [
        'name' => 'Updated Person',
        'email' => 'updated@example.com',
        'roles' => [$editorRole->id],
    ])
        ->assertOk()
        ->assertJsonPath('data.name', 'Updated Person')
        ->assertJsonPath('data.roles.0.name', 'editor');

    $updatedUser = User::query()->findOrFail($userId);
    expect($updatedUser->hasRole('editor'))->toBeTrue()
        ->and($updatedUser->hasRole('user'))->toBeFalse()
        ->and(Hash::check('password', $updatedUser->password))->toBeTrue();

    $this->deleteJson('/api/v1/admin/users/'.$userId)
        ->assertOk()
        ->assertJsonPath('message', 'User deleted successfully');

    $this->assertDatabaseMissing('users', ['id' => $userId]);
});

test('user api validates duplicate email invalid role and list filters', function () {
    $admin = accessControlApiActor('admin');
    $existing = User::factory()->create(['email' => 'existing@example.com']);
    $this->actingAs($admin, 'web');

    $this->postJson('/api/v1/admin/users', [
        'name' => 'Invalid User',
        'email' => $existing->email,
        'password' => 'password',
        'password_confirmation' => 'different-password',
        'roles' => [999999],
    ])
        ->assertUnprocessable()
        ->assertJsonPath('success', false)
        ->assertJsonPath('message', 'Validation failed')
        ->assertJsonValidationErrors(['email', 'password', 'roles.0']);

    $this->getJson('/api/v1/admin/users?role=missing-role&sort=password&per_page=101')
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['role', 'sort', 'per_page']);
});

test('admin can perform role crud and synchronize permissions', function () {
    $admin = accessControlApiActor('admin');
    $readPermission = Permission::findOrCreate('reports.read', 'web');
    $writePermission = Permission::findOrCreate('reports.write', 'web');
    $this->actingAs($admin, 'web');

    $created = $this->postJson('/api/v1/admin/roles', [
        'name' => 'analyst',
        'permissions' => [$readPermission->id],
    ])
        ->assertCreated()
        ->assertJsonPath('data.name', 'analyst')
        ->assertJsonPath('data.permissions.0.name', 'reports.read');

    $roleId = $created->json('data.id');

    $this->getJson('/api/v1/admin/roles?search=analyst&sort=name&direction=asc')
        ->assertOk()
        ->assertJsonPath('meta.total', 1)
        ->assertJsonPath('data.0.id', $roleId);

    $this->patchJson('/api/v1/admin/roles/'.$roleId, [
        'name' => 'senior-analyst',
        'permissions' => [$writePermission->id],
    ])
        ->assertOk()
        ->assertJsonPath('data.name', 'senior-analyst')
        ->assertJsonPath('data.permissions.0.name', 'reports.write');

    $role = Role::query()->findOrFail($roleId);
    expect($role->hasPermissionTo('reports.write'))->toBeTrue()
        ->and($role->hasPermissionTo('reports.read'))->toBeFalse();

    $this->getJson('/api/v1/admin/roles/options')
        ->assertOk()
        ->assertJsonFragment(['name' => 'senior-analyst']);

    $this->deleteJson('/api/v1/admin/roles/'.$roleId)
        ->assertOk()
        ->assertJsonPath('message', 'Role deleted successfully');

    $this->assertDatabaseMissing('roles', ['id' => $roleId]);
});

test('admin can perform permission crud and retrieve options', function () {
    $admin = accessControlApiActor('admin');
    $this->actingAs($admin, 'web');

    $created = $this->postJson('/api/v1/admin/permissions', [
        'name' => 'wallets.manage',
    ])
        ->assertCreated()
        ->assertJsonPath('data.name', 'wallets.manage')
        ->assertJsonPath('data.guard_name', 'web');

    $permissionId = $created->json('data.id');

    $this->getJson('/api/v1/admin/permissions?search=wallets&sort=name&direction=asc')
        ->assertOk()
        ->assertJsonPath('meta.total', 1)
        ->assertJsonPath('data.0.roles_count', 0);

    $this->patchJson('/api/v1/admin/permissions/'.$permissionId, [
        'name' => 'wallets.administer',
    ])
        ->assertOk()
        ->assertJsonPath('data.name', 'wallets.administer');

    $this->getJson('/api/v1/admin/permissions/options')
        ->assertOk()
        ->assertJsonFragment(['name' => 'wallets.administer']);

    $this->deleteJson('/api/v1/admin/permissions/'.$permissionId)
        ->assertOk()
        ->assertJsonPath('message', 'Permission deleted successfully');

    $this->assertDatabaseMissing('permissions', ['id' => $permissionId]);
});

test('role and permission api preserve unique and relation validation', function () {
    $admin = accessControlApiActor('admin');
    Role::findOrCreate('duplicate-role', 'web');
    Permission::findOrCreate('duplicate-permission', 'web');
    $this->actingAs($admin, 'web');

    $this->postJson('/api/v1/admin/roles', [
        'name' => 'duplicate-role',
        'permissions' => [999999],
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['name', 'permissions.0']);

    $this->postJson('/api/v1/admin/permissions', [
        'name' => 'duplicate-permission',
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['name']);
});
