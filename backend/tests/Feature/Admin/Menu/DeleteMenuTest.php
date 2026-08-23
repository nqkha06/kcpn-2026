<?php

use App\Models\Menu;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;
use function Pest\Laravel\deleteJson;
use function Pest\Laravel\withHeaders;

beforeEach(function (): void {
    withHeaders([
        'Accept' => 'application/json',
        'Origin' => 'http://localhost:3000',
        'Referer' => 'http://localhost:3000/admin',
    ]);
});

test('guests cannot delete a menu', function () {
    $menu = Menu::factory()->create();

    deleteJson('/api/v1/admin/menus/'.$menu->id)
        ->assertUnauthorized()
        ->assertJsonPath('message', 'Unauthenticated');

    assertDatabaseHas('menus', ['id' => $menu->id]);
});

test('non admin users cannot delete a menu', function () {
    $user = regularUser();
    $menu = Menu::factory()->create();

    actingAs($user, 'web')
        ->deleteJson('/api/v1/admin/menus/'.$menu->id)
        ->assertForbidden()
        ->assertJsonPath('message', 'Forbidden');

    assertDatabaseHas('menus', ['id' => $menu->id]);
});

test('admin can delete a menu item', function () {
    $admin = adminUser();

    $menu = Menu::factory()->create();

    actingAs($admin, 'web')
        ->deleteJson('/api/v1/admin/menus/'.$menu->id)
        ->assertOk()
        ->assertJsonPath('message', 'Menu deleted successfully');

    assertDatabaseMissing('menus', ['id' => $menu->id]);
});

test('deleting a non existent menu returns not found', function () {
    $admin = adminUser();

    actingAs($admin, 'web')
        ->deleteJson('/api/v1/admin/menus/999999')
        ->assertNotFound();
});

test('deleting a parent menu nulls out children parent_id', function () {
    $admin = adminUser();

    $parent = Menu::factory()->create();
    $child = Menu::factory()->create(['parent_id' => $parent->id]);

    actingAs($admin, 'web')
        ->deleteJson('/api/v1/admin/menus/'.$parent->id)
        ->assertOk();

    assertDatabaseMissing('menus', ['id' => $parent->id]);
    assertDatabaseHas('menus', ['id' => $child->id, 'parent_id' => null]);
});
