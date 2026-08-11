<?php

use App\Models\Menu;
use App\Models\User;
use Spatie\Permission\Models\Role;

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
    $user = User::factory()->create();
    $user->assignRole(Role::findOrCreate('user', 'web'));

    actingAs($user, 'web')
        ->getJson('/api/v1/admin/menus')
        ->assertForbidden()
        ->assertJsonPath('message', 'Forbidden');
});

test('admin can list menus with default pagination', function () {
    $admin = User::factory()->create();
    $admin->assignRole(Role::findOrCreate('admin', 'web'));

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
    $admin = User::factory()->create();
    $admin->assignRole(Role::findOrCreate('admin', 'web'));

    Menu::factory()->create(['title' => 'Pricing Plans', 'url' => '/pricing']);
    Menu::factory()->create(['title' => 'About Us', 'url' => '/about']);

    actingAs($admin, 'web')
        ->getJson('/api/v1/admin/menus?search=Pricing')
        ->assertOk()
        ->assertJsonPath('meta.total', 1)
        ->assertJsonPath('data.0.title', 'Pricing Plans');
});

test('admin can filter menus by status canonical and parent_id', function () {
    $admin = User::factory()->create();
    $admin->assignRole(Role::findOrCreate('admin', 'web'));

    $parent = Menu::factory()->header()->create();
    Menu::factory()->header()->create(['parent_id' => $parent->id, 'status' => 'active']);
    Menu::factory()->footer()->inactive()->create();

    actingAs($admin, 'web')
        ->getJson('/api/v1/admin/menus?status=active&canonical=home.header&parent_id='.$parent->id)
        ->assertOk()
        ->assertJsonPath('meta.total', 1);
});

test('admin can sort and paginate menus', function () {
    $admin = User::factory()->create();
    $admin->assignRole(Role::findOrCreate('admin', 'web'));

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
    $admin = User::factory()->create();
    $admin->assignRole(Role::findOrCreate('admin', 'web'));

    actingAs($admin, 'web')
        ->getJson('/api/v1/admin/menus?status=archived&sort=invalid_field&direction=up&per_page=500&parent_id=999999')
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['status', 'sort', 'direction', 'per_page', 'parent_id']);
});
