<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;
use function Pest\Laravel\postJson;

test('an admin can create a user', function () {
    actingAs(adminUser())
        ->postJson('/api/v1/admin/users', [
            'name' => 'API Managed User',
            'email' => 'managed@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'roles' => [],
        ])
        ->assertCreated()
        ->assertJsonPath('data.email', 'managed@example.com');

    assertDatabaseHas('users', ['email' => 'managed@example.com']);
});

test('a guest cannot create a user', function () {
    postJson('/api/v1/admin/users', [
        'name' => 'Guest Managed User',
        'email' => 'guest-managed@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ])->assertUnauthorized();

    assertDatabaseMissing('users', ['email' => 'guest-managed@example.com']);
});

test('a regular user cannot create a user', function () {
    actingAs(regularUser())
        ->postJson('/api/v1/admin/users', [
            'name' => 'Unauthorized Managed User',
            'email' => 'unauthorized-managed@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])
        ->assertForbidden();

    assertDatabaseMissing('users', ['email' => 'unauthorized-managed@example.com']);
});

test('user creation validates required fields', function () {
    actingAs(adminUser())
        ->postJson('/api/v1/admin/users', [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['name', 'email', 'password']);
});

test('user creation rejects an existing email', function () {
    User::factory()->create(['email' => 'existing@example.com']);

    actingAs(adminUser())
        ->postJson('/api/v1/admin/users', [
            'name' => 'Existing User',
            'email' => 'existing@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('email');

    expect(User::query()->where('email', 'existing@example.com')->count())->toBe(1);
});

test('user creation rejects invalid and duplicate role ids', function () {
    $role = Role::findOrCreate('manager', 'web');

    actingAs(adminUser())
        ->postJson('/api/v1/admin/users', [
            'name' => 'Role User',
            'email' => 'role-user@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'roles' => [$role->id, $role->id, 999999],
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['roles.1', 'roles.2']);

    assertDatabaseMissing('users', ['email' => 'role-user@example.com']);
});

test('an admin can create a user with roles and a hashed password', function () {
    $role = Role::findOrCreate('manager', 'web');

    actingAs(adminUser())
        ->postJson('/api/v1/admin/users', [
            'name' => 'Managed Role User',
            'email' => 'managed-role@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'roles' => [$role->id],
        ])
        ->assertCreated()
        ->assertJsonPath('data.roles.0.name', 'manager')
        ->assertJsonMissingPath('data.password');

    $user = User::query()->where('email', 'managed-role@example.com')->firstOrFail();

    expect($user->hasRole('manager'))->toBeTrue()
        ->and(Hash::check('password123', $user->password))->toBeTrue();
});
