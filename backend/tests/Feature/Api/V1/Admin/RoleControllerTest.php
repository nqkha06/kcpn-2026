<?php

use Spatie\Permission\Models\Role;
use function Pest\Laravel\actingAs;
use function Pest\Laravel\deleteJson;
use function Pest\Laravel\getJson;
use function Pest\Laravel\postJson;
use function Pest\Laravel\putJson;

beforeEach(function () {
    $this->admin = adminUser();
});

it('can list roles', function () {
    Role::findOrCreate('editor', 'web');

    actingAs($this->admin)
        ->getJson(route('api.v1.admin.roles.index'))
        ->assertOk()
        ->assertJsonStructure(['data', 'meta', 'links']);
});

it('can get role options', function () {
    actingAs($this->admin)
        ->getJson(route('api.v1.admin.roles.options'))
        ->assertOk()
        ->assertJsonStructure(['data' => [['id', 'name']]]);
});

it('can create a role', function () {
    actingAs($this->admin)
        ->postJson(route('api.v1.admin.roles.store'), [
            'name' => 'new-role',
            'permissions' => []
        ])
        ->assertCreated()
        ->assertJsonPath('data.name', 'new-role');

    expect(Role::where('name', 'new-role')->exists())->toBeTrue();
});

it('can view a role', function () {
    $role = Role::findOrCreate('view-role', 'web');

    actingAs($this->admin)
        ->getJson(route('api.v1.admin.roles.show', $role))
        ->assertOk()
        ->assertJsonPath('data.name', 'view-role');
});

it('can update a role', function () {
    $role = Role::findOrCreate('old-role', 'web');

    actingAs($this->admin)
        ->putJson(route('api.v1.admin.roles.update', $role), [
            'name' => 'updated-role',
            'permissions' => []
        ])
        ->assertOk()
        ->assertJsonPath('data.name', 'updated-role');

    expect($role->fresh()->name)->toBe('updated-role');
});

it('can delete a role', function () {
    $role = Role::findOrCreate('delete-role', 'web');

    actingAs($this->admin)
        ->deleteJson(route('api.v1.admin.roles.destroy', $role))
        ->assertOk();

    expect(Role::where('name', 'delete-role')->exists())->toBeFalse();
});
