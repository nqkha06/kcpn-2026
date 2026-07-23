<?php

use App\Models\User;
use Spatie\Permission\Models\Role;

function settingsApiUser(string $role = 'user'): User
{
    $user = User::factory()->create();
    $user->assignRole(Role::findOrCreate($role, 'web'));

    return $user;
}

beforeEach(function (): void {
    $this->withHeaders([
        'Accept' => 'application/json',
        'Origin' => 'http://localhost:3000',
        'Referer' => 'http://localhost:3000/user/settings',
    ]);
});

test('user can read profile preferences and currency options', function () {
    $user = settingsApiUser();
    $user->setMeta('currency', 'USD');

    $this->actingAs($user, 'web')
        ->getJson('/api/v1/user/settings')
        ->assertOk()
        ->assertJsonPath('data.profile.name', $user->name)
        ->assertJsonPath('data.profile.email', $user->email)
        ->assertJsonPath('data.preferences.currency', 'USD')
        ->assertJsonCount(4, 'data.currency_options');
});

test('user can update profile and changing email clears verification', function () {
    $user = settingsApiUser();

    $this->actingAs($user, 'web')
        ->patchJson('/api/v1/user/settings/profile', [
            'name' => 'Updated User',
            'email' => 'updated@example.com',
        ])
        ->assertOk()
        ->assertJsonPath('data.name', 'Updated User')
        ->assertJsonPath('data.email', 'updated@example.com')
        ->assertJsonPath('data.email_verified_at', null);

    expect($user->fresh()->email_verified_at)->toBeNull();
});

test('user can update currency preference and validation remains authoritative', function () {
    $user = settingsApiUser();
    $this->actingAs($user, 'web');

    $this->patchJson('/api/v1/user/settings/preferences', [
        'currency' => 'eur',
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('currency');

    $this->patchJson('/api/v1/user/settings/preferences', [
        'currency' => 'EUR',
    ])
        ->assertOk()
        ->assertJsonPath('data.preferences.currency', 'EUR');

    expect($user->getMeta('currency'))->toBe('EUR');
});

test('admin role cannot access user-only settings endpoints', function () {
    $admin = settingsApiUser('admin');

    $this->actingAs($admin, 'web')
        ->getJson('/api/v1/user/settings')
        ->assertForbidden()
        ->assertJsonPath('success', false)
        ->assertJsonPath('message', 'Forbidden');
});
