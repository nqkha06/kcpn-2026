<?php

use App\Models\Category;
use App\Models\User;
use App\Policies\CategoryPolicy;

test('category policy viewAny allows admin and regular user but rejects user without role', function () {
    $policy = new CategoryPolicy;
    $admin = adminUser();
    $user = regularUser();
    $guestUser = User::factory()->create();

    expect($policy->viewAny($admin))->toBeTrue()
        ->and($policy->viewAny($user))->toBeTrue()
        ->and($policy->viewAny($guestUser))->toBeFalse();
});

test('category policy view allows anyone for global categories and owner for private categories', function () {
    $policy = new CategoryPolicy;
    $admin = adminUser();
    $user = regularUser();
    $otherUser = User::factory()->create();

    $globalCategory = Category::factory()->create(['user_id' => null]);
    $privateCategory = Category::factory()->create(['user_id' => $user->id]);

    expect($policy->view($admin, $globalCategory))->toBeTrue()
        ->and($policy->view($user, $globalCategory))->toBeTrue()
        ->and($policy->view($otherUser, $globalCategory))->toBeTrue()
        ->and($policy->view($user, $privateCategory))->toBeTrue()
        ->and($policy->view($otherUser, $privateCategory))->toBeFalse();
});

test('category policy create allows admin and regular user but rejects user without role', function () {
    $policy = new CategoryPolicy;
    $admin = adminUser();
    $user = regularUser();
    $guestUser = User::factory()->create();

    expect($policy->create($admin))->toBeTrue()
        ->and($policy->create($user))->toBeTrue()
        ->and($policy->create($guestUser))->toBeFalse();
});

test('category policy update allows admin for global and owner for private', function () {
    $policy = new CategoryPolicy;
    $admin = adminUser();
    $user = regularUser();
    $otherUser = User::factory()->create();

    $globalCategory = Category::factory()->create(['user_id' => null]);
    $privateCategory = Category::factory()->create(['user_id' => $user->id]);

    // Global category: only admin
    expect($policy->update($admin, $globalCategory))->toBeTrue()
        ->and($policy->update($user, $globalCategory))->toBeFalse();

    // Private category: only owner
    expect($policy->update($user, $privateCategory))->toBeTrue()
        ->and($policy->update($otherUser, $privateCategory))->toBeFalse()
        ->and($policy->update($admin, $privateCategory))->toBeFalse();
});

test('category policy delete allows admin for global and owner for private', function () {
    $policy = new CategoryPolicy;
    $admin = adminUser();
    $user = regularUser();
    $otherUser = User::factory()->create();

    $globalCategory = Category::factory()->create(['user_id' => null]);
    $privateCategory = Category::factory()->create(['user_id' => $user->id]);

    expect($policy->delete($admin, $globalCategory))->toBeTrue()
        ->and($policy->delete($user, $globalCategory))->toBeFalse()
        ->and($policy->delete($user, $privateCategory))->toBeTrue()
        ->and($policy->delete($otherUser, $privateCategory))->toBeFalse()
        ->and($policy->delete($admin, $privateCategory))->toBeFalse();
});

test('category policy restore allows admin for global and owner for private', function () {
    $policy = new CategoryPolicy;
    $admin = adminUser();
    $user = regularUser();
    $otherUser = User::factory()->create();

    $globalCategory = Category::factory()->create(['user_id' => null]);
    $privateCategory = Category::factory()->create(['user_id' => $user->id]);

    expect($policy->restore($admin, $globalCategory))->toBeTrue()
        ->and($policy->restore($user, $globalCategory))->toBeFalse()
        ->and($policy->restore($user, $privateCategory))->toBeTrue()
        ->and($policy->restore($otherUser, $privateCategory))->toBeFalse()
        ->and($policy->restore($admin, $privateCategory))->toBeFalse();
});

test('category policy forceDelete allows admin for global and owner for private', function () {
    $policy = new CategoryPolicy;
    $admin = adminUser();
    $user = regularUser();
    $otherUser = User::factory()->create();

    $globalCategory = Category::factory()->create(['user_id' => null]);
    $privateCategory = Category::factory()->create(['user_id' => $user->id]);

    expect($policy->forceDelete($admin, $globalCategory))->toBeTrue()
        ->and($policy->forceDelete($user, $globalCategory))->toBeFalse()
        ->and($policy->forceDelete($user, $privateCategory))->toBeTrue()
        ->and($policy->forceDelete($otherUser, $privateCategory))->toBeFalse()
        ->and($policy->forceDelete($admin, $privateCategory))->toBeFalse();
});
