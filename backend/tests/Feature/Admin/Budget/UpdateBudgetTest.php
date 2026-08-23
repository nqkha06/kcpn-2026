<?php

use App\Models\Budget;
use App\Models\Category;
use App\Models\User;

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
    $user = regularUser();
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
    $admin = adminUser();

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
    $admin = adminUser();

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
    $admin = adminUser();

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
    $admin = adminUser();

    $budget = Budget::factory()->create();

    actingAs($admin, 'web')
        ->patchJson('/api/v1/admin/budgets/'.$budget->id, [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['user_id', 'category_id', 'amount_limit', 'period', 'status']);
});

test('updating a non existent budget returns not found', function () {
    $admin = adminUser();

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

test('reassigning a budget to another user who already has that category and period fails', function () {
    $admin = adminUser();

    $ownerA = User::factory()->create();
    $ownerB = User::factory()->create();
    $category = Category::factory()->create();

    $budget = Budget::factory()->for($ownerA)->for($category)->monthly()->create();
    Budget::factory()->for($ownerB)->for($category)->monthly()->create();

    actingAs($admin, 'web')
        ->patchJson('/api/v1/admin/budgets/'.$budget->id, [
            'user_id' => $ownerB->id,
            'category_id' => $category->id,
            'amount_limit' => 500,
            'period' => 'monthly',
            'status' => 'active',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['category_id']);
});

test('reassigning a budget to another user with no conflicting combination succeeds', function () {
    $admin = adminUser();

    $ownerA = User::factory()->create();
    $ownerB = User::factory()->create();
    $category = Category::factory()->create();

    $budget = Budget::factory()->for($ownerA)->for($category)->monthly()->create();

    actingAs($admin, 'web')
        ->patchJson('/api/v1/admin/budgets/'.$budget->id, [
            'user_id' => $ownerB->id,
            'category_id' => $category->id,
            'amount_limit' => 500,
            'period' => 'monthly',
            'status' => 'active',
        ])
        ->assertOk()
        ->assertJsonPath('data.user.id', $ownerB->id);

    assertDatabaseHas('budgets', ['id' => $budget->id, 'user_id' => $ownerB->id]);
});

// --- Boundary Value Analysis: amount_limit on update ---
dataset('update amount_limit boundaries', [
    'min boundary (0.01)' => [0.01, true],
    'below min (0.00)' => [0.00, false],
    'max boundary (9999999999.99)' => [9999999999.99, true],
    'above max (10000000000.00)' => [10000000000.00, false],
]);

test('budget update enforces amount_limit boundaries', function (float $amount, bool $shouldPass) {
    $admin = adminUser();
    $budget = Budget::factory()->create();

    $response = actingAs($admin, 'web')
        ->patchJson('/api/v1/admin/budgets/'.$budget->id, [
            'user_id' => $budget->user_id,
            'category_id' => $budget->category_id,
            'amount_limit' => $amount,
            'period' => $budget->period,
            'status' => 'active',
        ]);

    $shouldPass
        ? $response->assertOk()
        : $response->assertUnprocessable()->assertJsonValidationErrors(['amount_limit']);
})->with('update amount_limit boundaries');

// --- Decision table: status enum on update ---
dataset('update status equivalence classes', [
    'valid active' => ['active', true],
    'valid inactive' => ['inactive', true],
    'wrong case (Active)' => ['Active', false],
    'unsupported value (archived)' => ['archived', false],
]);

test('budget update enforces the status enum strictly', function (string $status, bool $shouldPass) {
    $admin = adminUser();
    $budget = Budget::factory()->create();

    $response = actingAs($admin, 'web')
        ->patchJson('/api/v1/admin/budgets/'.$budget->id, [
            'user_id' => $budget->user_id,
            'category_id' => $budget->category_id,
            'amount_limit' => 500,
            'period' => $budget->period,
            'status' => $status,
        ]);

    $shouldPass
        ? $response->assertOk()
        : $response->assertUnprocessable()->assertJsonValidationErrors(['status']);
})->with('update status equivalence classes');
