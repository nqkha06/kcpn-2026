<?php

use Spatie\Permission\Models\Permission;

use function Pest\Laravel\actingAs;

test('can list permissions', function () {
    Permission::findOrCreate('test-permission', 'web');

    actingAs(adminUser())
        ->getJson(route('api.v1.admin.permissions.index'))
        ->assertOk()
        ->assertJsonStructure(['data', 'meta', 'links']);
});

test('can get permission options', function () {
    Permission::findOrCreate('option-permission', 'web');

    actingAs(adminUser())
        ->getJson(route('api.v1.admin.permissions.options'))
        ->assertOk()
        ->assertJsonStructure(['data' => [['id', 'name']]]);
});

test('can create a permission', function () {
    actingAs(adminUser())
        ->postJson(route('api.v1.admin.permissions.store'), [
            'name' => 'new-permission',
            'group' => 'general',
        ])
        ->assertCreated()
        ->assertJsonPath('data.name', 'new-permission');

    expect(Permission::where('name', 'new-permission')->exists())->toBeTrue();
});

test('can view a permission', function () {
    $permission = Permission::findOrCreate('view-permission', 'web');

    actingAs(adminUser())
        ->getJson(route('api.v1.admin.permissions.show', $permission))
        ->assertOk()
        ->assertJsonPath('data.name', 'view-permission');
});

test('can update a permission', function () {
    $permission = Permission::findOrCreate('old-permission', 'web');

    actingAs(adminUser())
        ->putJson(route('api.v1.admin.permissions.update', $permission), [
            'name' => 'updated-permission',
            'group' => 'general',
        ])
        ->assertOk()
        ->assertJsonPath('data.name', 'updated-permission');

    expect($permission->fresh()->name)->toBe('updated-permission');
});

test('can delete a permission', function () {
    $permission = Permission::findOrCreate('delete-permission', 'web');

    actingAs(adminUser())
        ->deleteJson(route('api.v1.admin.permissions.destroy', $permission))
        ->assertOk();

    expect(Permission::where('name', 'delete-permission')->exists())->toBeFalse();
});
