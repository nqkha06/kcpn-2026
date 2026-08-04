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

test('guests cannot update a menu', function () {
    $menu = Menu::factory()->create();

    $this->patchJson('/api/v1/admin/menus/'.$menu->id, [
        'title' => 'Updated',
        'canonical' => 'home.header',
        'target' => '_self',
        'status' => 'active',
    ])
        ->assertUnauthorized()
        ->assertJsonPath('message', 'Unauthenticated');
});

test('non admin users cannot update a menu', function () {
    $user = User::factory()->create();
    $user->assignRole(Role::findOrCreate('user', 'web'));
    $menu = Menu::factory()->create();

    $this->actingAs($user, 'web')
        ->patchJson('/api/v1/admin/menus/'.$menu->id, [
            'title' => 'Updated',
            'canonical' => 'home.header',
            'target' => '_self',
            'status' => 'active',
        ])
        ->assertForbidden()
        ->assertJsonPath('message', 'Forbidden');
});

test('admin can update a menu item', function () {
    $admin = User::factory()->create();
    $admin->assignRole(Role::findOrCreate('admin', 'web'));

    $menu = Menu::factory()->create();

    $payload = [
        'title' => 'About Us',
        'url' => '',
        'canonical' => 'home.footer',
        'parent_id' => null,
        'sort_order' => 1,
        'target' => '_blank',
        'status' => 'inactive',
    ];

    $this->actingAs($admin, 'web')
        ->patchJson('/api/v1/admin/menus/'.$menu->id, $payload)
        ->assertOk()
        ->assertJsonPath('message', 'Menu updated successfully')
        ->assertJsonPath('data.title', 'About Us')
        ->assertJsonPath('data.url', null)
        ->assertJsonPath('data.canonical', 'home.footer')
        ->assertJsonPath('data.target', '_blank')
        ->assertJsonPath('data.status', 'inactive');

    $this->assertDatabaseHas('menus', [
        'id' => $menu->id,
        'title' => 'About Us',
        'url' => null,
        'canonical' => 'home.footer',
        'target' => '_blank',
        'status' => 'inactive',
    ]);
});

test('updating a menu to be its own parent is rejected', function () {
    $admin = User::factory()->create();
    $admin->assignRole(Role::findOrCreate('admin', 'web'));

    $menu = Menu::factory()->create();

    $this->actingAs($admin, 'web')
        ->patchJson('/api/v1/admin/menus/'.$menu->id, [
            'title' => $menu->title,
            'canonical' => $menu->canonical,
            'parent_id' => $menu->id,
            'target' => '_self',
            'status' => 'active',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['parent_id']);

    $this->assertDatabaseHas('menus', [
        'id' => $menu->id,
        'parent_id' => null,
    ]);
});

test('admin updating a menu with a new parent inherits the parent canonical', function () {
    $admin = User::factory()->create();
    $admin->assignRole(Role::findOrCreate('admin', 'web'));

    $parent = Menu::factory()->footer()->create();
    $menu = Menu::factory()->header()->create();

    $this->actingAs($admin, 'web')
        ->patchJson('/api/v1/admin/menus/'.$menu->id, [
            'title' => $menu->title,
            'canonical' => 'home.header',
            'parent_id' => $parent->id,
            'target' => '_self',
            'status' => 'active',
        ])
        ->assertOk()
        ->assertJsonPath('data.canonical', 'home.footer')
        ->assertJsonPath('data.parent.id', $parent->id);

    $this->assertDatabaseHas('menus', [
        'id' => $menu->id,
        'parent_id' => $parent->id,
        'canonical' => 'home.footer',
    ]);
});

test('menu update validates required fields', function () {
    $admin = User::factory()->create();
    $admin->assignRole(Role::findOrCreate('admin', 'web'));

    $menu = Menu::factory()->create();

    $this->actingAs($admin, 'web')
        ->patchJson('/api/v1/admin/menus/'.$menu->id, [
            'title' => '',
            'canonical' => '',
            'target' => 'invalid',
            'status' => 'invalid',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['title', 'canonical', 'target', 'status']);
});