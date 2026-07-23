<?php

use App\Models\User;

test('admin can view user index', function () {
    $admin = adminUser();

    $this->actingAs($admin);
    $this->withoutVite();

    $this->get(route('admin.users.index'))
        ->assertOk();
});

test('admin can create a user', function () {
    $admin = adminUser();

    $this->actingAs($admin);

    $payload = [
        'name' => 'Nguyen Van A',
        'email' => 'user-crud-create@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'roles' => [],
    ];

    $this->post(route('admin.users.store'), $payload)
        ->assertRedirect(route('admin.users.index'));

    $userExists = User::query()
        ->where('email', 'user-crud-create@example.com')
        ->where('name', 'Nguyen Van A')
        ->exists();

    expect($userExists)->toBeTrue();
});

test('admin can update a user', function () {
    $admin = adminUser();
    $user = User::factory()->create([
        'name' => 'Old Name',
        'email' => 'user-crud-update-old@example.com',
    ]);

    $this->actingAs($admin);

    $payload = [
        'name' => 'New Name',
        'email' => 'user-crud-update-new@example.com',
        'password' => null,
        'password_confirmation' => null,
        'roles' => [],
    ];

    $this->put(route('admin.users.update', $user), $payload)
        ->assertRedirect(route('admin.users.index'));

    $updatedExists = User::query()
        ->whereKey($user->id)
        ->where('name', 'New Name')
        ->where('email', 'user-crud-update-new@example.com')
        ->exists();

    expect($updatedExists)->toBeTrue();
});

test('admin can delete a user', function () {
    $admin = adminUser();
    $user = User::factory()->create();

    $this->actingAs($admin);

    $this->delete(route('admin.users.destroy', $user))
        ->assertRedirect(route('admin.users.index'));

    $userStillExists = User::query()
        ->whereKey($user->id)
        ->exists();

    expect($userStillExists)->toBeFalse();
});
