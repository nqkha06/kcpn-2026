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

test('guests cannot view a budget', function () {
    $budget = Budget::factory()->create();

    $this->getJson('/api/v1/admin/budgets/'.$budget->id)
        ->assertUnauthorized()
        ->assertJsonPath('message', 'Unauthenticated');
});

test('non admin users cannot view a budget', function () {
    $user = User::factory()->create();
    $user->assignRole(Role::findOrCreate('user', 'web'));
    $budget = Budget::factory()->create();

    $this->actingAs($user, 'web')
        ->getJson('/api/v1/admin/budgets/'.$budget->id)
        ->assertForbidden()
        ->assertJsonPath('message', 'Forbidden');
});

test('admin can view a single budget with user and category details', function () {
    $admin = User::factory()->create();
    $admin->assignRole(Role::findOrCreate('admin', 'web'));

    $customer = User::factory()->create(['name' => 'Budget Owner']);
    $category = Category::factory()->create(['name' => 'Groceries']);
    $budget = Budget::factory()->for($customer)->for($category)->create([
        'amount_limit' => 2000,
        'note' => 'Monthly groceries',
    ]);

    $this->actingAs($admin, 'web')
        ->getJson('/api/v1/admin/budgets/'.$budget->id)
        ->assertOk()
        ->assertJsonPath('data.id', $budget->id)
        ->assertJsonPath('data.amount_limit', 2000)
        ->assertJsonPath('data.note', 'Monthly groceries')
        ->assertJsonPath('data.user.id', $customer->id)
        ->assertJsonPath('data.user.name', 'Budget Owner')
        ->assertJsonPath('data.category.id', $category->id)
        ->assertJsonPath('data.category.name', 'Groceries');
});

test('admin viewing a budget receives the computed spent amount', function () {
    $admin = User::factory()->create();
    $admin->assignRole(Role::findOrCreate('admin', 'web'));

    $customer = User::factory()->create();
    $category = Category::factory()->create();
    $budget = Budget::factory()->for($customer)->for($category)->monthly()->create();

    ExpenseTransaction::factory()->forUser($customer)->for($category)
        ->expense()->posted()->create([
            'amount' => 75,
            'transacted_at' => now()->toDateString(),
        ]);

    $this->actingAs($admin, 'web')
        ->getJson('/api/v1/admin/budgets/'.$budget->id)
        ->assertOk()
        ->assertJsonPath('data.spent', 75);
});

test('viewing a non existent budget returns not found', function () {
    $admin = User::factory()->create();
    $admin->assignRole(Role::findOrCreate('admin', 'web'));

    $this->actingAs($admin, 'web')
        ->getJson('/api/v1/admin/budgets/999999')
        ->assertNotFound();
});