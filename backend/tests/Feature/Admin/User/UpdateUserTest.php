<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;
use function Pest\Laravel\patchJson;

test('an admin can update a user', function () {
    $user = User::factory()->create();

    actingAs(adminUser())
        ->patchJson("/api/v1/admin/users/{$user->id}", [
            'name' => 'Updated User',
            'email' => 'updated@example.com',
            'roles' => [],
        ])
        ->assertOk()
        ->assertJsonPath('data.name', 'Updated User');

    assertDatabaseHas('users', ['id' => $user->id, 'email' => 'updated@example.com']);
});

test('a guest cannot update a user', function () {
    $user = User::factory()->create();

    patchJson("/api/v1/admin/users/{$user->id}", [
        'name' => 'Guest Update',
        'email' => 'guest-update@example.com',
    ])->assertUnauthorized();

    assertDatabaseMissing('users', ['email' => 'guest-update@example.com']);
});

test('a regular user cannot update a user', function () {
    $user = User::factory()->create();

    actingAs(regularUser())
        ->patchJson("/api/v1/admin/users/{$user->id}", [
            'name' => 'Unauthorized Update',
            'email' => 'unauthorized-update@example.com',
        ])
        ->assertForbidden();
});

test('user update validates required and unique fields', function () {
    $user = User::factory()->create();
    User::factory()->create(['email' => 'existing@example.com']);

    actingAs(adminUser())
        ->patchJson("/api/v1/admin/users/{$user->id}", [
            'name' => '',
            'email' => 'existing@example.com',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['name', 'email']);
});

test('updating a user without a password keeps the current password', function () {
    $user = User::factory()->create(['password' => 'current-password']);
    $password = $user->password;

    actingAs(adminUser())
        ->patchJson("/api/v1/admin/users/{$user->id}", [
            'name' => 'Updated Name',
            'email' => $user->email,
            'password' => null,
        ])
        ->assertOk();

    expect($user->fresh()->password)->toBe($password);
});

test('an admin can update a users password and roles', function () {
    $user = User::factory()->create();
    $oldRole = Role::findOrCreate('old-role', 'web');
    $newRole = Role::findOrCreate('new-role', 'web');
    $user->assignRole($oldRole);

    actingAs(adminUser())
        ->patchJson("/api/v1/admin/users/{$user->id}", [
            'name' => $user->name,
            'email' => $user->email,
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
            'roles' => [$newRole->id],
        ])
        ->assertOk()
        ->assertJsonPath('data.roles.0.name', 'new-role');

    $user->refresh();

    expect(Hash::check('new-password', $user->password))->toBeTrue()
        ->and($user->hasRole('new-role'))->toBeTrue()
        ->and($user->hasRole('old-role'))->toBeFalse();
});

test('updating a user with an empty roles list removes their roles', function () {
    $user = regularUser();

    actingAs(adminUser())
        ->patchJson("/api/v1/admin/users/{$user->id}", [
            'name' => $user->name,
            'email' => $user->email,
            'roles' => [],
        ])
        ->assertOk()
        ->assertJsonCount(0, 'data.roles');

    expect($user->fresh()->roles)->toBeEmpty();
});

test('updating a missing user returns not found', function () {
    actingAs(adminUser())
        ->patchJson('/api/v1/admin/users/999999', [
            'name' => 'Missing User',
            'email' => 'missing-user@example.com',
        ])
        ->assertNotFound();
});
