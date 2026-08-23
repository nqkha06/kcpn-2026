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

test('guests cannot list menus', function () {
    getJson('/api/v1/admin/menus')
        ->assertUnauthorized()
        ->assertJsonPath('message', 'Unauthenticated');
});

test('non admin users cannot list menus', function () {
    $user = regularUser();

    actingAs($user, 'web')
        ->getJson('/api/v1/admin/menus')
        ->assertForbidden()
        ->assertJsonPath('message', 'Forbidden');
});

test('admin can list menus with default pagination', function () {
    $admin = adminUser();

    Menu::factory()->count(3)->create();

    actingAs($admin, 'web')
        ->getJson('/api/v1/admin/menus')
        ->assertOk()
        ->assertJsonStructure([
            'success',
            'message',
            'data' => [
                '*' => ['id', 'title', 'url', 'parent_id', 'canonical', 'sort_order', 'target', 'status'],
            ],
            'meta' => ['current_page', 'last_page', 'per_page', 'total'],
            'links' => ['first', 'last', 'prev', 'next'],
        ])
        ->assertJsonPath('meta.total', 3);
});

test('admin can search menus by title or url', function () {
    $admin = adminUser();

    Menu::factory()->create(['title' => 'Pricing Plans', 'url' => '/pricing']);
    Menu::factory()->create(['title' => 'About Us', 'url' => '/about']);

    actingAs($admin, 'web')
        ->getJson('/api/v1/admin/menus?search=Pricing')
        ->assertOk()
        ->assertJsonPath('meta.total', 1)
        ->assertJsonPath('data.0.title', 'Pricing Plans');
});

test('admin can filter menus by status canonical and parent_id', function () {
    $admin = adminUser();

    $parent = Menu::factory()->header()->create();
    Menu::factory()->header()->create(['parent_id' => $parent->id, 'status' => 'active']);
    Menu::factory()->footer()->inactive()->create();

    actingAs($admin, 'web')
        ->getJson('/api/v1/admin/menus?status=active&canonical=home.header&parent_id='.$parent->id)
        ->assertOk()
        ->assertJsonPath('meta.total', 1);
});

test('admin can filter menus by inactive status', function () {
    $admin = adminUser();

    Menu::factory()->create(['status' => 'active']);
    $inactive = Menu::factory()->inactive()->create();

    actingAs($admin, 'web')
        ->getJson('/api/v1/admin/menus?status=inactive')
        ->assertOk()
        ->assertJsonPath('meta.total', 1)
        ->assertJsonPath('data.0.id', $inactive->id);
});

test('admin can sort and paginate menus', function () {
    $admin = adminUser();

    Menu::factory()->create(['title' => 'Alpha', 'sort_order' => 3]);
    Menu::factory()->create(['title' => 'Bravo', 'sort_order' => 1]);
    Menu::factory()->create(['title' => 'Charlie', 'sort_order' => 2]);

    actingAs($admin, 'web')
        ->getJson('/api/v1/admin/menus?sort=title&direction=asc&per_page=2&page=1')
        ->assertOk()
        ->assertJsonPath('data.0.title', 'Alpha')
        ->assertJsonPath('data.1.title', 'Bravo')
        ->assertJsonPath('meta.per_page', 2)
        ->assertJsonPath('meta.total', 3)
        ->assertJsonPath('meta.last_page', 2);
});

test('menu index query parameters are validated', function () {
    $admin = adminUser();

    actingAs($admin, 'web')
        ->getJson('/api/v1/admin/menus?status=archived&sort=invalid_field&direction=up&per_page=500&parent_id=999999')
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['status', 'sort', 'direction', 'per_page', 'parent_id']);
});
