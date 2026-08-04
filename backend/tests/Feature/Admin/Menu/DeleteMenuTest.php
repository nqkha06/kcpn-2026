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

test('guests cannot delete a menu', function () {
    $menu = Menu::factory()->create();

    $this->deleteJson('/api/v1/admin/menus/'.$menu->id)
        ->assertUnauthorized()
        ->assertJsonPath('message', 'Unauthenticated');

    $this->assertDatabaseHas('menus', ['id' => $menu->id]);
});

test('non admin users cannot delete a menu', function () {
    $user = User::factory()->create();
    $user->assignRole(Role::findOrCreate('user', 'web'));
    $menu = Menu::factory()->create();

    $this->actingAs($user, 'web')
        ->deleteJson('/api/v1/admin/menus/'.$menu->id)
        ->assertForbidden()
        ->assertJsonPath('message', 'Forbidden');

    $this->assertDatabaseHas('menus', ['id' => $menu->id]);
});

test('admin can delete a menu item', function () {
    $admin = User::factory()->create();
    $admin->assignRole(Role::findOrCreate('admin', 'web'));

    $menu = Menu::factory()->create();

    $this->actingAs($admin, 'web')
        ->deleteJson('/api/v1/admin/menus/'.$menu->id)
        ->assertOk()
        ->assertJsonPath('message', 'Menu deleted successfully');

    $this->assertDatabaseMissing('menus', ['id' => $menu->id]);
});

test('deleting a non existent menu returns not found', function () {
    $admin = User::factory()->create();
    $admin->assignRole(Role::findOrCreate('admin', 'web'));

    $this->actingAs($admin, 'web')
        ->deleteJson('/api/v1/admin/menus/999999')
        ->assertNotFound();
});

test('deleting a parent menu nulls out children parent_id', function () {
    $admin = User::factory()->create();
    $admin->assignRole(Role::findOrCreate('admin', 'web'));

    $parent = Menu::factory()->create();
    $child = Menu::factory()->create(['parent_id' => $parent->id]);

    $this->actingAs($admin, 'web')
        ->deleteJson('/api/v1/admin/menus/'.$parent->id)
        ->assertOk();

    $this->assertDatabaseMissing('menus', ['id' => $parent->id]);
    $this->assertDatabaseHas('menus', ['id' => $child->id, 'parent_id' => null]);
});