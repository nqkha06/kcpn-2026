<?php

use App\Models\Budget;
use App\Policies\BudgetPolicy;

test('budget policy allows admin to restore and force delete any budget', function () {
    $admin = adminUser();
    $policy = new BudgetPolicy();
    $budget = Budget::factory()->make(['user_id' => regularUser()->id]);

    expect($policy->restore($admin, $budget))->toBeTrue();
    expect($policy->forceDelete($admin, $budget))->toBeTrue();
});

test('budget policy allows the owning user to restore and force delete their own budget', function () {
    $owner = regularUser();
    $policy = new BudgetPolicy();
    $budget = Budget::factory()->make(['user_id' => $owner->id]);

    expect($policy->restore($owner, $budget))->toBeTrue();
    expect($policy->forceDelete($owner, $budget))->toBeTrue();
});

test('budget policy denies a non owning regular user from restoring or force deleting', function () {
    $stranger = regularUser();
    $owner = regularUser();
    $policy = new BudgetPolicy();
    $budget = Budget::factory()->make(['user_id' => $owner->id]);

    expect($policy->restore($stranger, $budget))->toBeFalse();
    expect($policy->forceDelete($stranger, $budget))->toBeFalse();
});