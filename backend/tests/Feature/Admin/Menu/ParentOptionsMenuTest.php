<?php

use App\Models\Menu;
use App\Models\User;
use Spatie\Permission\Models\Role;

beforeEach(function (): void {
    $this->withHeaders([
        'Accept' => 'application/json',
        'Origin' => 'http://localhost:3000',
        'Referer' => 'http://localhost:3000/admin',
    ]);
});

test('guests cannot fetch parent menu options', function () {
    $this->getJson('/api/v1/admin/menus/parent-options')
        ->assertUnauthorized()
        ->assertJsonPath('message', 'Unauthenticated');
});

test('non admin users cannot fetch parent menu options', function () {
    $user = User::factory()->create();
    $user->assignRole(Role::findOrCreate('user', 'web'));

    $this->actingAs($user, 'web')
        ->getJson('/api/v1/admin/menus/parent-options')
        ->assertForbidden()
        ->assertJsonPath('message', 'Forbidden');
});

test('parent options only include root level menus ordered by title', function () {
    $admin = User::factory()->create();
    $admin->assignRole(Role::findOrCreate('admin', 'web'));

    $root1 = Menu::factory()->create(['title' => 'Zebra', 'parent_id' => null]);
    $root2 = Menu::factory()->create(['title' => 'Alpha', 'parent_id' => null]);
    $child = Menu::factory()->create(['title' => 'Child Menu', 'parent_id' => $root1->id]);

    $response = $this->actingAs($admin, 'web')
        ->getJson('/api/v1/admin/menus/parent-options')
        ->assertOk();

    $ids = collect($response->json('data'))->pluck('id')->all();

    expect($ids)->toContain($root1->id, $root2->id);
    expect($ids)->not->toContain($child->id);
    $response->assertJsonPath('data.0.id', $root2->id);
});

test('parent options exclude the given menu id', function () {
    $admin = User::factory()->create();
    $admin->assignRole(Role::findOrCreate('admin', 'web'));

    $menu = Menu::factory()->create(['parent_id' => null]);
    $other = Menu::factory()->create(['parent_id' => null]);

    $response = $this->actingAs($admin, 'web')
        ->getJson('/api/v1/admin/menus/parent-options?exclude='.$menu->id)
        ->assertOk();

    $ids = collect($response->json('data'))->pluck('id')->all();

    expect($ids)->not->toContain($menu->id);
    expect($ids)->toContain($other->id);
});

test('parent options exclude filter validates the menu exists', function () {
    $admin = User::factory()->create();
    $admin->assignRole(Role::findOrCreate('admin', 'web'));

    $this->actingAs($admin, 'web')
        ->getJson('/api/v1/admin/menus/parent-options?exclude=999999')
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['exclude']);
});