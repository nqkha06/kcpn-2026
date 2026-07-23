<?php

use App\Models\Budget;
use App\Models\Category;
use App\Models\User;

test('admin can view budget index', function () {
    $admin = adminUser();

    $this->actingAs($admin);
    $this->withoutVite();

    $this->get(route('admin.budgets.index'))
        ->assertOk();
});

test('admin can create a budget', function () {
    $admin = adminUser();
    $user = User::factory()->create();
    $category = Category::factory()->create(['status' => 'active']);

    $this->actingAs($admin);

    $payload = [
        'user_id' => $user->id,
        'category_id' => $category->id,
        'amount_limit' => 1500000,
        'period' => 'monthly',
        'status' => 'active',
        'note' => 'Monthly food budget',
    ];

    $this->post(route('admin.budgets.store'), $payload)
        ->assertRedirect(route('admin.budgets.index'));

    $budgetExists = Budget::query()
        ->where('user_id', $user->id)
        ->where('category_id', $category->id)
        ->where('period', 'monthly')
        ->where('status', 'active')
        ->exists();

    expect($budgetExists)->toBeTrue();
});

test('admin can update a budget', function () {
    $admin = adminUser();
    $user = User::factory()->create();
    $sourceCategory = Category::factory()->create();
    $targetCategory = Category::factory()->create();
    $budget = Budget::factory()->create([
        'user_id' => $user->id,
        'category_id' => $sourceCategory->id,
        'period' => 'monthly',
    ]);

    $this->actingAs($admin);

    $payload = [
        'user_id' => $user->id,
        'category_id' => $targetCategory->id,
        'amount_limit' => 2500000,
        'period' => 'yearly',
        'status' => 'inactive',
        'note' => 'Adjusted annual budget',
    ];

    $this->put(route('admin.budgets.update', $budget), $payload)
        ->assertRedirect(route('admin.budgets.index'));

    $updatedExists = Budget::query()
        ->whereKey($budget->id)
        ->where('category_id', $targetCategory->id)
        ->where('period', 'yearly')
        ->where('status', 'inactive')
        ->exists();

    expect($updatedExists)->toBeTrue();
});

test('admin can delete a budget', function () {
    $admin = adminUser();
    $budget = Budget::factory()->create();

    $this->actingAs($admin);

    $this->delete(route('admin.budgets.destroy', $budget))
        ->assertRedirect(route('admin.budgets.index'));

    $budgetStillExists = Budget::query()
        ->whereKey($budget->id)
        ->exists();

    expect($budgetStillExists)->toBeFalse();
});
