<?php

use App\Models\Category;
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

test('guests cannot fetch budget options', function () {
    getJson('/api/v1/admin/budgets/options')
        ->assertUnauthorized()
        ->assertJsonPath('message', 'Unauthenticated');
});

test('non admin users cannot fetch budget options', function () {
    $user = User::factory()->create();
    $user->assignRole(Role::findOrCreate('user', 'web'));

    actingAs($user, 'web')
        ->getJson('/api/v1/admin/budgets/options')
        ->assertForbidden()
        ->assertJsonPath('message', 'Forbidden');
});

test('admin can fetch budget options with users and active categories', function () {
    $admin = User::factory()->create();
    $admin->assignRole(Role::findOrCreate('admin', 'web'));

    $customer = User::factory()->create(['name' => 'Budget Customer', 'email' => 'customer@example.com']);
    $activeCategory = Category::factory()->create(['name' => 'Active Category', 'status' => 'active']);
    Category::factory()->inactive()->create(['name' => 'Hidden Category']);

    actingAs($admin, 'web')
        ->getJson('/api/v1/admin/budgets/options')
        ->assertOk()
        ->assertJsonFragment(['id' => $customer->id, 'name' => 'Budget Customer', 'email' => 'customer@example.com'])
        ->assertJsonFragment(['id' => $activeCategory->id, 'name' => 'Active Category'])
        ->assertJsonMissing(['name' => 'Hidden Category'])
        ->assertJsonPath('data.periods', ['monthly', 'yearly'])
        ->assertJsonPath('data.statuses', ['active', 'inactive']);
});
