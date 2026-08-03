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

test('guests cannot view a menu', function () {
    $menu = Menu::factory()->create();

    $this->getJson('/api/v1/admin/menus/'.$menu->id)
        ->assertUnauthorized()
        ->assertJsonPath('message', 'Unauthenticated');
});

test('non admin users cannot view a menu', function () {
    $user = User::factory()->create();
    $user->assignRole(Role::findOrCreate('user', 'web'));
    $menu = Menu::factory()->create();

    $this->actingAs($user, 'web')
        ->getJson('/api/v1/admin/menus/'.$menu->id)
        ->assertForbidden()
        ->assertJsonPath('message', 'Forbidden');
});

test('admin can view a single menu', function () {
    $admin = User::factory()->create();
    $admin->assignRole(Role::findOrCreate('admin', 'web'));

    $menu = Menu::factory()->create([
        'title' => 'Contact',
        'url' => '/contact',
        'canonical' => 'home.footer',
    ]);

    $this->actingAs($admin, 'web')
        ->getJson('/api/v1/admin/menus/'.$menu->id)
        ->assertOk()
        ->assertJsonPath('data.id', $menu->id)
        ->assertJsonPath('data.title', 'Contact')
        ->assertJsonPath('data.url', '/contact')
        ->assertJsonPath('data.canonical', 'home.footer');
});

test('admin viewing a menu with a parent receives parent details', function () {
    $admin = User::factory()->create();
    $admin->assignRole(Role::findOrCreate('admin', 'web'));

    $parent = Menu::factory()->header()->create(['title' => 'Main Menu']);
    $child = Menu::factory()->header()->create([
        'parent_id' => $parent->id,
        'title' => 'Sub Menu',
    ]);

    $this->actingAs($admin, 'web')
        ->getJson('/api/v1/admin/menus/'.$child->id)
        ->assertOk()
        ->assertJsonPath('data.parent.id', $parent->id)
        ->assertJsonPath('data.parent.title', 'Main Menu');
});

test('viewing a non existent menu returns not found', function () {
    $admin = User::factory()->create();
    $admin->assignRole(Role::findOrCreate('admin', 'web'));

    $this->actingAs($admin, 'web')
        ->getJson('/api/v1/admin/menus/999999')
        ->assertNotFound();
});