<?php

use App\Models\Budget;
use App\Models\Category;
use App\Models\User;
use Spatie\Permission\Models\Role;

beforeEach(function (): void {
    $this->withHeaders([
        'Accept' => 'application/json',
        'Origin' => 'http://localhost:3000',
        'Referer' => 'http://localhost:3000/user/budgets',
    ]);
});

test('guests cannot create a budget', function () {
    $category = Category::factory()->create();

    $this->postJson('/api/v1/user/budgets', [
        'category_id' => $category->id,
        'amount_limit' => 1000,
        'period' => 'monthly',
    ])
        ->assertUnauthorized()
        ->assertJsonPath('message', 'Unauthenticated');
});

test('user can create a budget for their own account', function () {
    $user = User::factory()->create();
    $user->assignRole(Role::findOrCreate('user', 'web'));
    $category = Category::factory()->create();

    $payload = [
        'category_id' => $category->id,
        'amount_limit' => 2500000,
        'period' => 'yearly',
        'note' => '  Annual target  ',
    ];

    $response = $this->actingAs($user, 'web')
        ->postJson('/api/v1/user/budgets', $payload)
        ->assertCreated()
        ->assertJsonPath('message', 'Budget created successfully')
        ->assertJsonPath('data.category_id', $category->id)
        ->assertJsonPath('data.amount_limit', 2500000)
        ->assertJsonPath('data.period', 'yearly')
        ->assertJsonPath('data.note', 'Annual target')
        ->assertJsonPath('data.spent', 0)
        ->assertJsonPath('data.category.id', $category->id);

    $this->assertDatabaseHas('budgets', [
        'id' => $response->json('data.id'),
        'user_id' => $user->id,
        'category_id' => $category->id,
        'amount_limit' => 2500000,
        'period' => 'yearly',
        'status' => 'active',
        'note' => 'Annual target',
    ]);
});

test('created budget always starts as active regardless of input', function () {
    $user = User::factory()->create();
    $user->assignRole(Role::findOrCreate('user', 'web'));
    $category = Category::factory()->create();

    $response = $this->actingAs($user, 'web')
        ->postJson('/api/v1/user/budgets', [
            'category_id' => $category->id,
            'amount_limit' => 100,
            'period' => 'monthly',
            'status' => 'inactive',
        ])
        ->assertCreated()
        ->assertJsonPath('data.status', 'active');

    $this->assertDatabaseHas('budgets', [
        'id' => $response->json('data.id'),
        'status' => 'active',
    ]);
});

test('empty note is stored as null', function () {
    $user = User::factory()->create();
    $user->assignRole(Role::findOrCreate('user', 'web'));
    $category = Category::factory()->create();

    $response = $this->actingAs($user, 'web')
        ->postJson('/api/v1/user/budgets', [
            'category_id' => $category->id,
            'amount_limit' => 100,
            'period' => 'monthly',
            'note' => '   ',
        ])
        ->assertCreated()
        ->assertJsonPath('data.note', null);

    $this->assertDatabaseHas('budgets', [
        'id' => $response->json('data.id'),
        'note' => null,
    ]);
});

test('user can create one budget per category and period', function () {
    $user = User::factory()->create();
    $user->assignRole(Role::findOrCreate('user', 'web'));
    $category = Category::factory()->create();

    $payload = [
        'category_id' => $category->id,
        'amount_limit' => 2500000,
        'period' => 'yearly',
    ];

    $this->actingAs($user, 'web')
        ->postJson('/api/v1/user/budgets', $payload)
        ->assertCreated();

    $this->postJson('/api/v1/user/budgets', $payload)
        ->assertUnprocessable()
        ->assertJsonValidationErrors('category_id');
});

test('user can create the same category with a different period', function () {
    $user = User::factory()->create();
    $user->assignRole(Role::findOrCreate('user', 'web'));
    $category = Category::factory()->create();

    Budget::factory()->for($user)->for($category)->monthly()->create();

    $this->actingAs($user, 'web')
        ->postJson('/api/v1/user/budgets', [
            'category_id' => $category->id,
            'amount_limit' => 100,
            'period' => 'yearly',
        ])
        ->assertCreated();
});

test('budget creation requires category_id amount_limit and period', function () {
    $user = User::factory()->create();
    $user->assignRole(Role::findOrCreate('user', 'web'));

    $this->actingAs($user, 'web')
        ->postJson('/api/v1/user/budgets', [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['category_id', 'amount_limit', 'period']);
});

test('budget creation validates amount_limit range', function () {
    $user = User::factory()->create();
    $user->assignRole(Role::findOrCreate('user', 'web'));
    $category = Category::factory()->create();

    $this->actingAs($user, 'web')
        ->postJson('/api/v1/user/budgets', [
            'category_id' => $category->id,
            'amount_limit' => 0,
            'period' => 'monthly',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['amount_limit']);
});

test('budget creation validates period enum', function () {
    $user = User::factory()->create();
    $user->assignRole(Role::findOrCreate('user', 'web'));
    $category = Category::factory()->create();

    $this->actingAs($user, 'web')
        ->postJson('/api/v1/user/budgets', [
            'category_id' => $category->id,
            'amount_limit' => 100,
            'period' => 'weekly',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['period']);
});

test('budget creation rejects inactive categories', function () {
    $user = User::factory()->create();
    $user->assignRole(Role::findOrCreate('user', 'web'));
    $category = Category::factory()->inactive()->create();

    $this->actingAs($user, 'web')
        ->postJson('/api/v1/user/budgets', [
            'category_id' => $category->id,
            'amount_limit' => 100,
            'period' => 'monthly',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['category_id']);
});

test('budget creation rejects categories owned by another user', function () {
    $user = User::factory()->create();
    $user->assignRole(Role::findOrCreate('user', 'web'));
    $otherUser = User::factory()->create();
    $privateCategory = Category::factory()->create(['user_id' => $otherUser->id]);

    $this->actingAs($user, 'web')
        ->postJson('/api/v1/user/budgets', [
            'category_id' => $privateCategory->id,
            'amount_limit' => 100,
            'period' => 'monthly',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['category_id']);
});

test('budget creation accepts shared categories with no owner', function () {
    $user = User::factory()->create();
    $user->assignRole(Role::findOrCreate('user', 'web'));
    $sharedCategory = Category::factory()->create(['user_id' => null]);

    $this->actingAs($user, 'web')
        ->postJson('/api/v1/user/budgets', [
            'category_id' => $sharedCategory->id,
            'amount_limit' => 100,
            'period' => 'monthly',
        ])
        ->assertCreated();
});