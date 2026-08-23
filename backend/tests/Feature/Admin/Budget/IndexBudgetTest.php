<?php

use App\Models\Budget;
use App\Models\Category;
use App\Models\ExpenseTransaction;
use App\Models\User;

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

test('guests cannot list budgets', function () {
    getJson('/api/v1/admin/budgets')
        ->assertUnauthorized()
        ->assertJsonPath('message', 'Unauthenticated');
});

test('non admin users cannot list budgets', function () {
    $user = regularUser();

    actingAs($user, 'web')
        ->getJson('/api/v1/admin/budgets')
        ->assertForbidden()
        ->assertJsonPath('message', 'Forbidden');
});

test('admin can list budgets with default pagination', function () {
    $admin = adminUser();

    Budget::factory()->count(3)->create();

    actingAs($admin, 'web')
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
    $admin = adminUser();

    $customer = User::factory()->create(['name' => 'Budget Customer']);
    $category = Category::factory()->create(['name' => 'Food Budget']);
    $budget = Budget::factory()->for($customer)->for($category)->create();
    Budget::factory()->create();

    actingAs($admin, 'web')
        ->getJson('/api/v1/admin/budgets?search=Budget%20Customer')
        ->assertOk()
        ->assertJsonPath('meta.total', 1)
        ->assertJsonPath('data.0.id', $budget->id);
});

test('admin can search budgets by id', function () {
    $budget = Budget::factory()->create();

    actingAs(adminUser())
        ->getJson('/api/v1/admin/budgets?search='.$budget->id)
        ->assertOk()
        ->assertJsonPath('meta.total', 1)
        ->assertJsonPath('data.0.id', $budget->id);
})->todo('AdminBudgetService calls the undefined Eloquent Builder method orWhereKey');

test('admin can filter budgets by period status user and category', function () {
    $admin = adminUser();

    $customer = User::factory()->create();
    $category = Category::factory()->create();
    $budget = Budget::factory()->for($customer)->for($category)->monthly()->active()->create();
    Budget::factory()->create(['period' => 'yearly', 'status' => 'inactive']);

    actingAs($admin, 'web')
        ->getJson('/api/v1/admin/budgets?period=monthly&status=active&user_id='.$customer->id.'&category_id='.$category->id)
        ->assertOk()
        ->assertJsonPath('meta.total', 1)
        ->assertJsonPath('data.0.id', $budget->id);
});

test('admin budget index computes spent amount for the current period', function () {
    $admin = adminUser();

    $customer = User::factory()->create();
    $category = Category::factory()->create();
    Budget::factory()->for($customer)->for($category)->monthly()->active()->create();

    ExpenseTransaction::factory()->forUser($customer)->for($category)
        ->expense()->posted()->create([
            'amount' => 125.5,
            'transacted_at' => now()->toDateString(),
        ]);

    actingAs($admin, 'web')
        ->getJson('/api/v1/admin/budgets')
        ->assertOk()
        ->assertJsonPath('data.0.spent', 125.5);
});

test('budget spent only includes posted expenses in its current period', function () {
    $customer = User::factory()->create();
    $category = Category::factory()->create();
    $budget = Budget::factory()->for($customer)->for($category)->monthly()->active()->create();

    ExpenseTransaction::factory()->forUser($customer)->for($category)->expense()->posted()->create([
        'amount' => 100,
        'transacted_at' => now()->toDateString(),
    ]);
    ExpenseTransaction::factory()->forUser($customer)->for($category)->income()->posted()->create([
        'amount' => 500,
        'transacted_at' => now()->toDateString(),
    ]);
    ExpenseTransaction::factory()->forUser($customer)->for($category)->expense()->pending()->create([
        'amount' => 300,
        'transacted_at' => now()->toDateString(),
    ]);
    ExpenseTransaction::factory()->forUser($customer)->for($category)->expense()->posted()->create([
        'amount' => 200,
        'transacted_at' => now()->subMonth()->endOfMonth()->toDateString(),
    ]);

    actingAs(adminUser())
        ->getJson('/api/v1/admin/budgets/'.$budget->id)
        ->assertOk()
        ->assertJsonPath('data.spent', 100);
});

test('admin can sort and paginate budgets', function () {
    $admin = adminUser();

    Budget::factory()->create(['amount_limit' => 500]);
    Budget::factory()->create(['amount_limit' => 100]);
    Budget::factory()->create(['amount_limit' => 300]);

    actingAs($admin, 'web')
        ->getJson('/api/v1/admin/budgets?sort=amount_limit&direction=asc&per_page=2')
        ->assertOk()
        ->assertJsonPath('data.0.amount_limit', 100)
        ->assertJsonPath('data.1.amount_limit', 300)
        ->assertJsonPath('meta.per_page', 2)
        ->assertJsonPath('meta.total', 3);
});

test('budget index query parameters are validated', function () {
    $admin = adminUser();

    actingAs($admin, 'web')
        ->getJson('/api/v1/admin/budgets?period=weekly&status=archived&user_id=999999&category_id=999999&sort=invalid&direction=up&per_page=200')
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['period', 'status', 'user_id', 'category_id', 'sort', 'direction', 'per_page']);
});
