<?php

use App\Models\Budget;
use App\Models\Category;
use App\Models\User;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\patchJson;
use function Pest\Laravel\withHeaders;

beforeEach(function (): void {
    withHeaders([
        'Accept' => 'application/json',
        'Origin' => 'http://localhost:3000',
        'Referer' => 'http://localhost:3000/admin',
    ]);
});

test('guests cannot update a budget', function () {
    $budget = Budget::factory()->create();

    patchJson('/api/v1/admin/budgets/'.$budget->id, [
        'user_id' => $budget->user_id,
        'category_id' => $budget->category_id,
        'amount_limit' => 1500,
        'period' => 'yearly',
        'status' => 'inactive',
    ])
        ->assertUnauthorized()
        ->assertJsonPath('message', 'Unauthenticated');
});

test('non admin users cannot update a budget', function () {
    $user = User::factory()->create();
    $user->assignRole(Role::findOrCreate('user', 'web'));
    $budget = Budget::factory()->create();

    actingAs($user, 'web')
        ->patchJson('/api/v1/admin/budgets/'.$budget->id, [
            'user_id' => $budget->user_id,
            'category_id' => $budget->category_id,
            'amount_limit' => 1500,
            'period' => 'yearly',
            'status' => 'inactive',
        ])
        ->assertForbidden()
        ->assertJsonPath('message', 'Forbidden');
});

test('admin can update a budget', function () {
    $admin = User::factory()->create();
    $admin->assignRole(Role::findOrCreate('admin', 'web'));

    $customer = User::factory()->create();
    $category = Category::factory()->create();
    $budget = Budget::factory()->for($customer)->for($category)->monthly()->active()->create([
        'amount_limit' => 1000,
    ]);

    $payload = [
        'user_id' => $customer->id,
        'category_id' => $category->id,
        'amount_limit' => 1500,
        'period' => 'yearly',
        'status' => 'inactive',
        'note' => '',
    ];

    actingAs($admin, 'web')
        ->patchJson('/api/v1/admin/budgets/'.$budget->id, $payload)
        ->assertOk()
        ->assertJsonPath('message', 'Budget updated successfully')
        ->assertJsonPath('data.amount_limit', 1500)
        ->assertJsonPath('data.period', 'yearly')
        ->assertJsonPath('data.status', 'inactive')
        ->assertJsonPath('data.note', null);

    assertDatabaseHas('budgets', [
        'id' => $budget->id,
        'amount_limit' => 1500,
        'period' => 'yearly',
        'status' => 'inactive',
        'note' => null,
    ]);
});

test('updating a budget to duplicate an existing user category period combination fails', function () {
    $admin = User::factory()->create();
    $admin->assignRole(Role::findOrCreate('admin', 'web'));

    $customer = User::factory()->create();
    $category = Category::factory()->create();
    $otherCategory = Category::factory()->create();
    Budget::factory()->for($customer)->for($category)->monthly()->create();
    $otherBudget = Budget::factory()->for($customer)->for($otherCategory)->create(['period' => 'yearly']);

    actingAs($admin, 'web')
        ->patchJson('/api/v1/admin/budgets/'.$otherBudget->id, [
            'user_id' => $customer->id,
            'category_id' => $category->id,
            'amount_limit' => 500,
            'period' => 'monthly',
            'status' => 'active',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['category_id']);
});

test('a budget can keep its own combination when updated', function () {
    $admin = User::factory()->create();
    $admin->assignRole(Role::findOrCreate('admin', 'web'));

    $customer = User::factory()->create();
    $category = Category::factory()->create();
    $budget = Budget::factory()->for($customer)->for($category)->monthly()->create([
        'amount_limit' => 800,
    ]);

    actingAs($admin, 'web')
        ->patchJson('/api/v1/admin/budgets/'.$budget->id, [
            'user_id' => $customer->id,
            'category_id' => $category->id,
            'amount_limit' => 900,
            'period' => 'monthly',
            'status' => 'active',
        ])
        ->assertOk()
        ->assertJsonPath('data.amount_limit', 900);
});

test('budget update validates required fields', function () {
    $admin = User::factory()->create();
    $admin->assignRole(Role::findOrCreate('admin', 'web'));

    $budget = Budget::factory()->create();

    actingAs($admin, 'web')
        ->patchJson('/api/v1/admin/budgets/'.$budget->id, [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['user_id', 'category_id', 'amount_limit', 'period', 'status']);
});

test('updating a non existent budget returns not found', function () {
    $admin = User::factory()->create();
    $admin->assignRole(Role::findOrCreate('admin', 'web'));

    $customer = User::factory()->create();
    $category = Category::factory()->create();

    actingAs($admin, 'web')
        ->patchJson('/api/v1/admin/budgets/999999', [
            'user_id' => $customer->id,
            'category_id' => $category->id,
            'amount_limit' => 500,
            'period' => 'monthly',
            'status' => 'active',
        ])
        ->assertNotFound();
});
