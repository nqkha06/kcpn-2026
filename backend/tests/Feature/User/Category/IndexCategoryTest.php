<?php

use App\Models\Category;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\getJson;

test('a user can list visible categories', function () {
    $user = regularUser();
    $otherUser = regularUser();
    $global = Category::factory()->create(['name' => 'Global Category']);
    $private = Category::factory()->create(['user_id' => $user->id, 'name' => 'Private Category']);
    Category::factory()->create(['user_id' => $otherUser->id, 'name' => 'Hidden Category']);

    actingAs($user)
        ->getJson('/api/v1/user/categories')
        ->assertOk()
        ->assertJsonCount(2, 'data')
        ->assertJsonFragment(['id' => $global->id])
        ->assertJsonFragment(['id' => $private->id])
        ->assertJsonMissing(['name' => 'Hidden Category']);
});

test('a guest cannot list categories', function () {
    getJson('/api/v1/user/categories')->assertUnauthorized();
});

test('inactive categories are not listed', function () {
    $user = regularUser();
    Category::factory()->inactive()->create(['name' => 'Inactive Global']);
    Category::factory()->inactive()->create([
        'user_id' => $user->id,
        'name' => 'Inactive Private',
    ]);

    actingAs($user)
        ->getJson('/api/v1/user/categories')
        ->assertOk()
        ->assertJsonMissing(['name' => 'Inactive Global'])
        ->assertJsonMissing(['name' => 'Inactive Private']);
});

test('global categories are listed before private categories', function () {
    $user = regularUser();
    $private = Category::factory()->create([
        'user_id' => $user->id,
        'name' => 'Alpha Private',
    ]);
    $global = Category::factory()->create(['name' => 'Zulu Global']);

    actingAs($user)
        ->getJson('/api/v1/user/categories')
        ->assertOk()
        ->assertJsonPath('data.0.id', $global->id)
        ->assertJsonPath('data.1.id', $private->id);
});
