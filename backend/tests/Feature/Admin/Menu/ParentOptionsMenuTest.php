<?php

use App\Models\Menu;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\getJson;
use function Pest\Laravel\withHeaders;

beforeEach(function (): void {
    withHeaders([
        'Accept' => 'application/json',
        'Origin' => 'http://localhost:3000',
        'Referer' => 'http://localhost:3000/admin',
    ]);
});

test('guests cannot fetch parent menu options', function () {
    getJson('/api/v1/admin/menus/parent-options')
        ->assertUnauthorized()
        ->assertJsonPath('message', 'Unauthenticated');
});

test('non admin users cannot fetch parent menu options', function () {
    $user = regularUser();

    actingAs($user, 'web')
        ->getJson('/api/v1/admin/menus/parent-options')
        ->assertForbidden()
        ->assertJsonPath('message', 'Forbidden');
});

test('parent options only include root level menus ordered by title', function () {
    $admin = adminUser();

    $root1 = Menu::factory()->create(['title' => 'Zebra', 'parent_id' => null]);
    $root2 = Menu::factory()->create(['title' => 'Alpha', 'parent_id' => null]);
    $child = Menu::factory()->create(['title' => 'Child Menu', 'parent_id' => $root1->id]);

    $response = actingAs($admin, 'web')
        ->getJson('/api/v1/admin/menus/parent-options')
        ->assertOk();

    $ids = collect($response->json('data'))->pluck('id')->all();

    expect($ids)->toContain($root1->id, $root2->id);
    expect($ids)->not->toContain($child->id);
    $response->assertJsonPath('data.0.id', $root2->id);
});

test('parent options exclude the given menu id', function () {
    $admin = adminUser();

    $menu = Menu::factory()->create(['parent_id' => null]);
    $other = Menu::factory()->create(['parent_id' => null]);

    $response = actingAs($admin, 'web')
        ->getJson('/api/v1/admin/menus/parent-options?exclude='.$menu->id)
        ->assertOk();

    $ids = collect($response->json('data'))->pluck('id')->all();

    expect($ids)->not->toContain($menu->id);
    expect($ids)->toContain($other->id);
});

test('parent options exclude filter validates the menu exists', function () {
    $admin = adminUser();

    actingAs($admin, 'web')
        ->getJson('/api/v1/admin/menus/parent-options?exclude=999999')
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['exclude']);
});

test('parent options include inactive root menus, not just active ones', function () {
    // Decision table note: AdminMenuService::parentOptions() has no status filter, so a
    // disabled root menu can still be picked as a parent for a new child. This is
    // recorded as documented behaviour, not a defect -- flag for product owner
    // confirmation if inactive sections should be un-selectable as parents.
    $admin = adminUser();

    $inactiveRoot = Menu::factory()->inactive()->create(['title' => 'Disabled Section', 'parent_id' => null]);

    $response = actingAs($admin, 'web')
        ->getJson('/api/v1/admin/menus/parent-options')
        ->assertOk();

    expect(collect($response->json('data'))->pluck('id')->all())->toContain($inactiveRoot->id);
});

test('parent options only ever return root menus even when every menu is nested', function () {
    $admin = adminUser();

    Menu::factory()->count(3)->create(['parent_id' => null])->each(
        fn (Menu $root) => Menu::factory()->create(['parent_id' => $root->id])
    );

    $response = actingAs($admin, 'web')
        ->getJson('/api/v1/admin/menus/parent-options')
        ->assertOk();

    expect($response->json('data'))->toHaveCount(3);
});
