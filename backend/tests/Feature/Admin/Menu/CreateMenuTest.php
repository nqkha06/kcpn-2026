<?php

use App\Models\Menu;
use App\Models\User;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\postJson;
use function Pest\Laravel\withHeaders;

beforeEach(function (): void {
    withHeaders([
        'Accept' => 'application/json',
        'Origin' => 'http://localhost:3000',
        'Referer' => 'http://localhost:3000/admin',
    ]);
});

test('guests cannot create a menu', function () {
    postJson('/api/v1/admin/menus', [
        'title' => 'Pricing',
        'canonical' => 'home.header',
        'target' => '_self',
        'status' => 'active',
    ])
        ->assertUnauthorized()
        ->assertJsonPath('message', 'Unauthenticated');
});

test('non admin users cannot create a menu', function () {
    $user = User::factory()->create();
    $user->assignRole(Role::findOrCreate('user', 'web'));

    actingAs($user, 'web')
        ->postJson('/api/v1/admin/menus', [
            'title' => 'Pricing',
            'canonical' => 'home.header',
            'target' => '_self',
            'status' => 'active',
        ])
        ->assertForbidden()
        ->assertJsonPath('message', 'Forbidden');
});

test('admin can create a top level menu and it is persisted', function () {
    $admin = User::factory()->create();
    $admin->assignRole(Role::findOrCreate('admin', 'web'));

    $payload = [
        'title' => 'Pricing',
        'url' => '/pricing',
        'canonical' => 'home.header',
        'parent_id' => null,
        'sort_order' => 2,
        'target' => '_self',
        'status' => 'active',
    ];

    $response = actingAs($admin, 'web')
        ->postJson('/api/v1/admin/menus', $payload)
        ->assertCreated()
        ->assertJsonPath('message', 'Menu created successfully')
        ->assertJsonPath('data.title', 'Pricing')
        ->assertJsonPath('data.url', '/pricing')
        ->assertJsonPath('data.canonical', 'home.header')
        ->assertJsonPath('data.sort_order', 2)
        ->assertJsonPath('data.target', '_self')
        ->assertJsonPath('data.status', 'active');

    assertDatabaseHas('menus', [
        'id' => $response->json('data.id'),
        'title' => 'Pricing',
        'url' => '/pricing',
        'canonical' => 'home.header',
        'parent_id' => null,
        'sort_order' => 2,
        'target' => '_self',
        'status' => 'active',
    ]);
});

test('admin creating a child menu inherits the parent canonical', function () {
    $admin = User::factory()->create();
    $admin->assignRole(Role::findOrCreate('admin', 'web'));

    $parent = Menu::factory()->header()->create(['title' => 'Main']);

    $payload = [
        'title' => 'About',
        'url' => ' /p/about ',
        'canonical' => 'home.footer',
        'parent_id' => $parent->id,
        'sort_order' => 5,
        'target' => '_self',
        'status' => 'active',
    ];

    $response = actingAs($admin, 'web')
        ->postJson('/api/v1/admin/menus', $payload)
        ->assertCreated()
        ->assertJsonPath('data.url', '/p/about')
        ->assertJsonPath('data.canonical', 'home.header')
        ->assertJsonPath('data.parent.id', $parent->id);

    assertDatabaseHas('menus', [
        'id' => $response->json('data.id'),
        'parent_id' => $parent->id,
        'canonical' => 'home.header',
        'url' => '/p/about',
    ]);
});

test('menu creation requires title canonical target and status', function () {
    $admin = User::factory()->create();
    $admin->assignRole(Role::findOrCreate('admin', 'web'));

    actingAs($admin, 'web')
        ->postJson('/api/v1/admin/menus', [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['title', 'canonical', 'target', 'status']);
});

test('menu creation validates canonical format target and status values', function () {
    $admin = User::factory()->create();
    $admin->assignRole(Role::findOrCreate('admin', 'web'));

    actingAs($admin, 'web')
        ->postJson('/api/v1/admin/menus', [
            'title' => 'Broken',
            'canonical' => 'Not A Valid Canonical!',
            'target' => 'blank',
            'status' => 'archived',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['canonical', 'target', 'status']);
});

test('menu creation validates parent_id exists', function () {
    $admin = User::factory()->create();
    $admin->assignRole(Role::findOrCreate('admin', 'web'));

    actingAs($admin, 'web')
        ->postJson('/api/v1/admin/menus', [
            'title' => 'Broken Parent',
            'canonical' => 'home.header',
            'parent_id' => 999999,
            'target' => '_self',
            'status' => 'active',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['parent_id']);
});

test('menu creation rejects executable javascript urls', function () {
    actingAs(adminUser())
        ->postJson('/api/v1/admin/menus', [
            'title' => 'Unsafe Link',
            'url' => 'javascript:alert(document.domain)',
            'canonical' => 'home.header',
            'target' => '_self',
            'status' => 'active',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('url');
})->todo('MenuRequest accepts executable URL schemes that may be rendered as public links');
