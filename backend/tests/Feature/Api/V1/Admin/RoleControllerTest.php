<?php

use Spatie\Permission\Models\Role;

use function Pest\Laravel\actingAs;

test('can list roles', function () {
    Role::findOrCreate('editor', 'web');

    actingAs(adminUser())
        ->getJson(route('api.v1.admin.roles.index'))
        ->assertOk()
        ->assertJsonStructure(['data', 'meta', 'links']);
});

test('can get role options', function () {
    actingAs(adminUser())
        ->getJson(route('api.v1.admin.roles.options'))
        ->assertOk()
        ->assertJsonStructure(['data' => [['id', 'name']]]);
});

test('can create a role', function () {
    actingAs(adminUser())
        ->postJson(route('api.v1.admin.roles.store'), [
            'name' => 'new-role',
            'permissions' => [],
        ])
        ->assertCreated()
        ->assertJsonPath('data.name', 'new-role');

    expect(Role::where('name', 'new-role')->exists())->toBeTrue();
});

test('can view a role', function () {
    $role = Role::findOrCreate('view-role', 'web');

    actingAs(adminUser())
        ->getJson(route('api.v1.admin.roles.show', $role))
        ->assertOk()
        ->assertJsonPath('data.name', 'view-role');
});

test('can update a role', function () {
    $role = Role::findOrCreate('old-role', 'web');

    actingAs(adminUser())
        ->putJson(route('api.v1.admin.roles.update', $role), [
            'name' => 'updated-role',
            'permissions' => [],
        ])
        ->assertOk()
        ->assertJsonPath('data.name', 'updated-role');

    expect($role->fresh()->name)->toBe('updated-role');
});

test('can delete a role', function () {
    $role = Role::findOrCreate('delete-role', 'web');

    actingAs(adminUser())
        ->deleteJson(route('api.v1.admin.roles.destroy', $role))
        ->assertOk();

    expect(Role::where('name', 'delete-role')->exists())->toBeFalse();
});
