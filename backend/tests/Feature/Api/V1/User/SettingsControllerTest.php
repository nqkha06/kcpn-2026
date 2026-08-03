<?php

use App\Models\User;
use function Pest\Laravel\actingAs;
use function Pest\Laravel\getJson;
use function Pest\Laravel\patchJson;

beforeEach(function () {
    $this->user = regularUser();
});

it('can view user settings', function () {
    actingAs($this->user)
        ->getJson(route('api.v1.user.settings.show'))
        ->assertOk()
        ->assertJsonStructure(['data' => ['id', 'name', 'email']]);
});

it('can update user profile', function () {
    actingAs($this->user)
        ->patchJson(route('api.v1.user.settings.profile.update'), [
            'name' => 'Updated Name',
            'email' => 'updated@example.com'
        ])
        ->assertOk()
        ->assertJsonPath('data.name', 'Updated Name');

    expect($this->user->fresh()->name)->toBe('Updated Name');
});

it('can update user preferences', function () {
    actingAs($this->user)
        ->patchJson(route('api.v1.user.settings.preferences.update'), [
            'currency' => 'VND'
        ])
        ->assertOk();
});
