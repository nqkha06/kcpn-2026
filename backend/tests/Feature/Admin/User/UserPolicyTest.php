<?php

use App\Models\User;
use App\Policies\UserPolicy;

test('user policy methods allow admin and reject regular user', function () {
    $policy = new UserPolicy;
    $admin = adminUser();
    $user = regularUser();
    $targetUser = User::factory()->create();

    expect($policy->viewAny($admin))->toBeTrue()
        ->and($policy->viewAny($user))->toBeFalse()
        ->and($policy->view($admin, $targetUser))->toBeTrue()
        ->and($policy->view($user, $targetUser))->toBeFalse()
        ->and($policy->create($admin))->toBeTrue()
        ->and($policy->create($user))->toBeFalse()
        ->and($policy->update($admin, $targetUser))->toBeTrue()
        ->and($policy->update($user, $targetUser))->toBeFalse()
        ->and($policy->delete($admin, $targetUser))->toBeTrue()
        ->and($policy->delete($user, $targetUser))->toBeFalse()
        ->and($policy->restore($admin, $targetUser))->toBeTrue()
        ->and($policy->restore($user, $targetUser))->toBeFalse()
        ->and($policy->forceDelete($admin, $targetUser))->toBeTrue()
        ->and($policy->forceDelete($user, $targetUser))->toBeFalse();
});
