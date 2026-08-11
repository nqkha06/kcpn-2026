<?php

use App\Models\Budget;
use App\Models\Category;
use App\Models\ExpenseTransaction;
use App\Models\User;
use Spatie\Permission\Models\Role;

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

test('guests cannot create a budget', function () {
    $customer = User::factory()->create();
    $category = Category::factory()->create();

    postJson('/api/v1/admin/budgets', [
        'user_id' => $customer->id,
        'category_id' => $category->id,
        'amount_limit' => 1000,
        'period' => 'monthly',
        'status' => 'active',
    ])
        ->assertUnauthorized()
        ->assertJsonPath('message', 'Unauthenticated');
});

test('non admin users cannot create a budget', function () {
    $user = User::factory()->create();
    $user->assignRole(Role::findOrCreate('user', 'web'));
    $customer = User::factory()->create();
    $category = Category::factory()->create();

    actingAs($user, 'web')
        ->postJson('/api/v1/admin/budgets', [
            'user_id' => $customer->id,
            'category_id' => $category->id,
            'amount_limit' => 1000,
            'period' => 'monthly',
            'status' => 'active',
        ])
        ->assertForbidden()
        ->assertJsonPath('message', 'Forbidden');
});

test('admin can create a budget and it is persisted with spent amount', function () {
    $admin = User::factory()->create();
    $admin->assignRole(Role::findOrCreate('admin', 'web'));

    $customer = User::factory()->create(['name' => 'Budget Customer']);
    $category = Category::factory()->create(['name' => 'Food Budget']);

    ExpenseTransaction::factory()->forUser($customer)->for($category)
        ->expense()->posted()->create([
            'amount' => 125.5,
            'transacted_at' => now()->toDateString(),
        ]);

    $payload = [
        'user_id' => $customer->id,
        'category_id' => $category->id,
        'amount_limit' => 1000,
        'period' => 'monthly',
        'status' => 'active',
        'note' => ' Monthly food ',
    ];

    $response = actingAs($admin, 'web')
        ->postJson('/api/v1/admin/budgets', $payload)
        ->assertCreated()
        ->assertJsonPath('message', 'Budget created successfully')
        ->assertJsonPath('data.amount_limit', 1000)
        ->assertJsonPath('data.spent', 125.5)
        ->assertJsonPath('data.period', 'monthly')
        ->assertJsonPath('data.status', 'active')
        ->assertJsonPath('data.note', 'Monthly food');

    assertDatabaseHas('budgets', [
        'id' => $response->json('data.id'),
        'user_id' => $customer->id,
        'category_id' => $category->id,
        'amount_limit' => 1000,
        'period' => 'monthly',
        'status' => 'active',
        'note' => 'Monthly food',
    ]);
});

test('empty note is stored as null', function () {
    $admin = User::factory()->create();
    $admin->assignRole(Role::findOrCreate('admin', 'web'));

    $customer = User::factory()->create();
    $category = Category::factory()->create();

    $response = actingAs($admin, 'web')
        ->postJson('/api/v1/admin/budgets', [
            'user_id' => $customer->id,
            'category_id' => $category->id,
            'amount_limit' => 500,
            'period' => 'yearly',
            'status' => 'active',
            'note' => '   ',
        ])
        ->assertCreated()
        ->assertJsonPath('data.note', null);

    assertDatabaseHas('budgets', [
        'id' => $response->json('data.id'),
        'note' => null,
    ]);
});

test('budget creation requires user_id category_id amount_limit period and status', function () {
    $admin = User::factory()->create();
    $admin->assignRole(Role::findOrCreate('admin', 'web'));

    actingAs($admin, 'web')
        ->postJson('/api/v1/admin/budgets', [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['user_id', 'category_id', 'amount_limit', 'period', 'status']);
});

test('budget creation validates user_id and category_id exist', function () {
    $admin = User::factory()->create();
    $admin->assignRole(Role::findOrCreate('admin', 'web'));

    actingAs($admin, 'web')
        ->postJson('/api/v1/admin/budgets', [
            'user_id' => 999999,
            'category_id' => 999999,
            'amount_limit' => 500,
            'period' => 'monthly',
            'status' => 'active',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['user_id', 'category_id']);
});

test('budget creation validates amount_limit range', function () {
    $admin = User::factory()->create();
    $admin->assignRole(Role::findOrCreate('admin', 'web'));

    $customer = User::factory()->create();
    $category = Category::factory()->create();

    actingAs($admin, 'web')
        ->postJson('/api/v1/admin/budgets', [
            'user_id' => $customer->id,
            'category_id' => $category->id,
            'amount_limit' => 0,
            'period' => 'monthly',
            'status' => 'active',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['amount_limit']);
});

test('budget creation validates period and status enums', function () {
    $admin = User::factory()->create();
    $admin->assignRole(Role::findOrCreate('admin', 'web'));

    $customer = User::factory()->create();
    $category = Category::factory()->create();

    actingAs($admin, 'web')
        ->postJson('/api/v1/admin/budgets', [
            'user_id' => $customer->id,
            'category_id' => $category->id,
            'amount_limit' => 500,
            'period' => 'weekly',
            'status' => 'archived',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['period', 'status']);
});

test('budget creation prevents duplicate user category and period combination', function () {
    $admin = User::factory()->create();
    $admin->assignRole(Role::findOrCreate('admin', 'web'));

    $customer = User::factory()->create();
    $category = Category::factory()->create();
    Budget::factory()->for($customer)->for($category)->monthly()->create();

    actingAs($admin, 'web')
        ->postJson('/api/v1/admin/budgets', [
            'user_id' => $customer->id,
            'category_id' => $category->id,
            'amount_limit' => 200,
            'period' => 'monthly',
            'status' => 'active',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['category_id']);
});

test('budget creation validates category belongs to selected user or is shared', function () {
    $admin = User::factory()->create();
    $admin->assignRole(Role::findOrCreate('admin', 'web'));

    $customer = User::factory()->create();
    $otherUser = User::factory()->create();
    $privateCategory = Category::factory()->create(['user_id' => $otherUser->id]);

    actingAs($admin, 'web')
        ->postJson('/api/v1/admin/budgets', [
            'user_id' => $customer->id,
            'category_id' => $privateCategory->id,
            'amount_limit' => 500,
            'period' => 'monthly',
            'status' => 'active',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['category_id']);
});
