<?php

use App\Models\Budget;
use App\Models\Category;
use App\Models\ExpenseTransaction;
use App\Models\User;
use Spatie\Permission\Models\Role;

beforeEach(function (): void {
    $this->withHeaders([
        'Accept' => 'application/json',
        'Origin' => 'http://localhost:3000',
        'Referer' => 'http://localhost:3000/admin',
    ]);
});

test('guests cannot list budgets', function () {
    $this->getJson('/api/v1/admin/budgets')
        ->assertUnauthorized()
        ->assertJsonPath('message', 'Unauthenticated');
});

test('non admin users cannot list budgets', function () {
    $user = User::factory()->create();
    $user->assignRole(Role::findOrCreate('user', 'web'));

    $this->actingAs($user, 'web')
        ->getJson('/api/v1/admin/budgets')
        ->assertForbidden()
        ->assertJsonPath('message', 'Forbidden');
});

test('admin can list budgets with default pagination', function () {
    $admin = User::factory()->create();
    $admin->assignRole(Role::findOrCreate('admin', 'web'));

    Budget::factory()->count(3)->create();

    $this->actingAs($admin, 'web')
        ->getJson('/api/v1/admin/budgets')
        ->assertOk()
        ->assertJsonStructure([
            'success',
            'message',
            'data' => [
                '*' => ['id', 'user_id', 'category_id', 'amount_limit', 'spent', 'period', 'status', 'note'],
            ],
            'meta' => ['current_page', 'last_page', 'per_page', 'total'],
        ])
        ->assertJsonPath('meta.total', 3);
});

test('admin can search budgets by user name email category name note or id', function () {
    $admin = User::factory()->create();
    $admin->assignRole(Role::findOrCreate('admin', 'web'));

    $customer = User::factory()->create(['name' => 'Budget Customer']);
    $category = Category::factory()->create(['name' => 'Food Budget']);
    $budget = Budget::factory()->for($customer)->for($category)->create();
    Budget::factory()->create();

    $this->actingAs($admin, 'web')
        ->getJson('/api/v1/admin/budgets?search=Budget%20Customer')
        ->assertOk()
        ->assertJsonPath('meta.total', 1)
        ->assertJsonPath('data.0.id', $budget->id);
});

test('admin can filter budgets by period status user and category', function () {
    $admin = User::factory()->create();
    $admin->assignRole(Role::findOrCreate('admin', 'web'));

    $customer = User::factory()->create();
    $category = Category::factory()->create();
    $budget = Budget::factory()->for($customer)->for($category)->monthly()->active()->create();
    Budget::factory()->create(['period' => 'yearly', 'status' => 'inactive']);

    $this->actingAs($admin, 'web')
        ->getJson('/api/v1/admin/budgets?period=monthly&status=active&user_id='.$customer->id.'&category_id='.$category->id)
        ->assertOk()
        ->assertJsonPath('meta.total', 1)
        ->assertJsonPath('data.0.id', $budget->id);
});

test('admin budget index computes spent amount for the current period', function () {
    $admin = User::factory()->create();
    $admin->assignRole(Role::findOrCreate('admin', 'web'));

    $customer = User::factory()->create();
    $category = Category::factory()->create();
    Budget::factory()->for($customer)->for($category)->monthly()->active()->create();

    ExpenseTransaction::factory()->forUser($customer)->for($category)
        ->expense()->posted()->create([
            'amount' => 125.5,
            'transacted_at' => now()->toDateString(),
        ]);

    $this->actingAs($admin, 'web')
        ->getJson('/api/v1/admin/budgets')
        ->assertOk()
        ->assertJsonPath('data.0.spent', 125.5);
});

test('admin can sort and paginate budgets', function () {
    $admin = User::factory()->create();
    $admin->assignRole(Role::findOrCreate('admin', 'web'));

    Budget::factory()->create(['amount_limit' => 500]);
    Budget::factory()->create(['amount_limit' => 100]);
    Budget::factory()->create(['amount_limit' => 300]);

    $this->actingAs($admin, 'web')
        ->getJson('/api/v1/admin/budgets?sort=amount_limit&direction=asc&per_page=2')
        ->assertOk()
        ->assertJsonPath('data.0.amount_limit', 100)
        ->assertJsonPath('data.1.amount_limit', 300)
        ->assertJsonPath('meta.per_page', 2)
        ->assertJsonPath('meta.total', 3);
});

test('budget index query parameters are validated', function () {
    $admin = User::factory()->create();
    $admin->assignRole(Role::findOrCreate('admin', 'web'));

    $this->actingAs($admin, 'web')
        ->getJson('/api/v1/admin/budgets?period=weekly&status=archived&user_id=999999&category_id=999999&sort=invalid&direction=up&per_page=200')
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['period', 'status', 'user_id', 'category_id', 'sort', 'direction', 'per_page']);
});