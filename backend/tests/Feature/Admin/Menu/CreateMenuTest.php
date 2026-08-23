<?php

use App\Models\Menu;

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
    $user = regularUser();

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
    $admin = adminUser();

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
    $admin = adminUser();

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
    $admin = adminUser();

    actingAs($admin, 'web')
        ->postJson('/api/v1/admin/menus', [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['title', 'canonical', 'target', 'status']);
});

test('menu creation validates canonical format target and status values', function () {
    $admin = adminUser();

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
    $admin = adminUser();

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

// --- Boundary Value Analysis: title (rule: required, string, max:120) ---
dataset('title boundaries', [
    'single character' => [str_repeat('a', 1), true],
    'max boundary (120)' => [str_repeat('a', 120), true],
    'just above max (121)' => [str_repeat('a', 121), false],
    'well above max (200)' => [str_repeat('a', 200), false],
]);

test('menu creation enforces title length boundaries', function (string $title, bool $shouldPass) {
    $response = actingAs(adminUser())
        ->postJson('/api/v1/admin/menus', [
            'title' => $title,
            'canonical' => 'home.header',
            'target' => '_self',
            'status' => 'active',
        ]);

    $shouldPass
        ? $response->assertCreated()
        : $response->assertUnprocessable()->assertJsonValidationErrors(['title']);
})->with('title boundaries');

test('menu creation rejects an empty title', function () {
    actingAs(adminUser())
        ->postJson('/api/v1/admin/menus', [
            'title' => '',
            'canonical' => 'home.header',
            'target' => '_self',
            'status' => 'active',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['title']);
});

// --- Boundary Value Analysis: url (rule: nullable, string, max:255) ---
dataset('url boundaries', [
    'max boundary (255)' => [str_repeat('a', 255), true],
    'just above max (256)' => [str_repeat('a', 256), false],
]);

test('menu creation enforces url length boundaries', function (string $url, bool $shouldPass) {
    $response = actingAs(adminUser())
        ->postJson('/api/v1/admin/menus', [
            'title' => 'Boundary Url',
            'url' => $url,
            'canonical' => 'home.header',
            'target' => '_self',
            'status' => 'active',
        ]);

    $shouldPass
        ? $response->assertCreated()
        : $response->assertUnprocessable()->assertJsonValidationErrors(['url']);
})->with('url boundaries');

test('menu creation accepts a completely absent url', function () {
    actingAs(adminUser())
        ->postJson('/api/v1/admin/menus', [
            'title' => 'No Url',
            'canonical' => 'home.header',
            'target' => '_self',
            'status' => 'active',
        ])
        ->assertCreated()
        ->assertJsonPath('data.url', null);
});

// --- Decision table: canonical format (rule: regex ^[a-z0-9]+(\.[a-z0-9_-]+)+$, max:80) ---
dataset('canonical formats', [
    'valid two-segment (home.header)' => ['home.header', true],
    'valid with underscore segment (user.side_bar)' => ['user.side_bar', true],
    'valid with hyphen segment (home.footer-links)' => ['home.footer-links', true],
    'invalid: no dot separator (homeheader)' => ['homeheader', false],
    'invalid: uppercase letters (Home.Header)' => ['Home.Header', false],
    'invalid: spaces (home header)' => ['home header', false],
    'invalid: trailing dot (home.)' => ['home.', false],
    'invalid: leading dot (.header)' => ['.header', false],
    'invalid: special characters (home.header!)' => ['home.header!', false],
]);

test('menu creation enforces the canonical format decision table', function (string $canonical, bool $shouldPass) {
    $response = actingAs(adminUser())
        ->postJson('/api/v1/admin/menus', [
            'title' => 'Canonical Check',
            'canonical' => $canonical,
            'target' => '_self',
            'status' => 'active',
        ]);

    $shouldPass
        ? $response->assertCreated()
        : $response->assertUnprocessable()->assertJsonValidationErrors(['canonical']);
})->with('canonical formats');

test('menu creation enforces the canonical max length of 80', function () {
    actingAs(adminUser())
        ->postJson('/api/v1/admin/menus', [
            'title' => 'Long Canonical',
            'canonical' => 'home.'.str_repeat('a', 80),
            'target' => '_self',
            'status' => 'active',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['canonical']);
});

// --- Decision table: status enum (rule: required, in:active,inactive) ---
dataset('status equivalence classes', [
    'valid active' => ['active', true],
    'valid inactive' => ['inactive', true],
    'wrong case (Active)' => ['Active', false],
    'unsupported value (archived)' => ['archived', false],
    'empty string' => ['', false],
]);

test('menu creation enforces the status enum strictly', function (string $status, bool $shouldPass) {
    $response = actingAs(adminUser())
        ->postJson('/api/v1/admin/menus', [
            'title' => 'Status Check',
            'canonical' => 'home.header',
            'target' => '_self',
            'status' => $status,
        ]);

    $shouldPass
        ? $response->assertCreated()
        : $response->assertUnprocessable()->assertJsonValidationErrors(['status']);
})->with('status equivalence classes');

// --- Menu cha/con (parent/child) cases ---
test('a menu created without a parent_id becomes a root level menu', function () {
    actingAs(adminUser())
        ->postJson('/api/v1/admin/menus', [
            'title' => 'Root Menu',
            'canonical' => 'home.header',
            'target' => '_self',
            'status' => 'active',
        ])
        ->assertCreated()
        ->assertJsonPath('data.parent_id', null)
        ->assertJsonPath('data.parent', null);
});

test('a child menu can itself become a parent for a grandchild menu at the database level', function () {
    // Decision table note: parentOptions() only surfaces root-level menus (parent_id
    // IS NULL), so the admin UI cannot pick a child as a new parent. The store
    // endpoint itself, however, does not enforce a maximum depth -- only that a menu
    // cannot be its own parent (see 'updating a menu to be its own parent is rejected').
    // This test documents that a 3-level chain (root -> child -> grandchild) is
    // currently possible via a direct API call.
    $root = Menu::factory()->create(['title' => 'Root']);
    $child = Menu::factory()->create(['title' => 'Child', 'parent_id' => $root->id]);

    actingAs(adminUser())
        ->postJson('/api/v1/admin/menus', [
            'title' => 'Grandchild',
            'canonical' => 'home.header',
            'parent_id' => $child->id,
            'target' => '_self',
            'status' => 'active',
        ])
        ->assertCreated()
        ->assertJsonPath('data.parent.id', $child->id);
})->todo('Menu hierarchy depth is unbounded at the API level even though parent-options only offers root menus, allowing menus deeper than the 2-level structure the admin UI is designed for');

test('multiple child menus can share the same parent and each inherits its canonical', function () {
    $parent = Menu::factory()->footer()->create(['title' => 'Footer Root']);
    $admin = adminUser();

    $first = actingAs($admin, 'web')->postJson('/api/v1/admin/menus', [
        'title' => 'Footer Child One',
        'canonical' => 'home.header',
        'parent_id' => $parent->id,
        'target' => '_self',
        'status' => 'active',
    ])->assertCreated();

    $second = actingAs($admin, 'web')->postJson('/api/v1/admin/menus', [
        'title' => 'Footer Child Two',
        'canonical' => 'user.header',
        'parent_id' => $parent->id,
        'target' => '_self',
        'status' => 'active',
    ])->assertCreated();

    expect($first->json('data.canonical'))->toBe('home.footer');
    expect($second->json('data.canonical'))->toBe('home.footer');

    assertDatabaseHas('menus', ['id' => $first->json('data.id'), 'parent_id' => $parent->id]);
    assertDatabaseHas('menus', ['id' => $second->json('data.id'), 'parent_id' => $parent->id]);
});
