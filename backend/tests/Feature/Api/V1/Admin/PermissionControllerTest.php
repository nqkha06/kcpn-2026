<?php

use Spatie\Permission\Models\Permission;
use function Pest\Laravel\actingAs;
use function Pest\Laravel\deleteJson;
use function Pest\Laravel\getJson;
use function Pest\Laravel\postJson;
use function Pest\Laravel\putJson;

beforeEach(function () {
    $this->admin = adminUser();
});

it('can list permissions', function () {
    Permission::findOrCreate('test-permission', 'web');

    actingAs($this->admin)
        ->getJson(route('api.v1.admin.permissions.index'))
        ->assertOk()
        ->assertJsonStructure(['data', 'meta', 'links']);
});

it('can get permission options', function () {
    Permission::findOrCreate('option-permission', 'web');

    actingAs($this->admin)
        ->getJson(route('api.v1.admin.permissions.options'))
        ->assertOk()
        ->assertJsonStructure(['data' => [['id', 'name']]]);
});

it('can create a permission', function () {
    actingAs($this->admin)
        ->postJson(route('api.v1.admin.permissions.store'), [
            'name' => 'new-permission',
            'group' => 'general'
        ])
        ->assertCreated()
        ->assertJsonPath('data.name', 'new-permission');

    expect(Permission::where('name', 'new-permission')->exists())->toBeTrue();
});

it('can view a permission', function () {
    $permission = Permission::findOrCreate('view-permission', 'web');

    actingAs($this->admin)
        ->getJson(route('api.v1.admin.permissions.show', $permission))
        ->assertOk()
        ->assertJsonPath('data.name', 'view-permission');
});

it('can update a permission', function () {
    $permission = Permission::findOrCreate('old-permission', 'web');

    actingAs($this->admin)
        ->putJson(route('api.v1.admin.permissions.update', $permission), [
            'name' => 'updated-permission',
            'group' => 'general'
        ])
        ->assertOk()
        ->assertJsonPath('data.name', 'updated-permission');

    expect($permission->fresh()->name)->toBe('updated-permission');
});

it('can delete a permission', function () {
    $permission = Permission::findOrCreate('delete-permission', 'web');

    actingAs($this->admin)
        ->deleteJson(route('api.v1.admin.permissions.destroy', $permission))
        ->assertOk();

    expect(Permission::where('name', 'delete-permission')->exists())->toBeFalse();
});
