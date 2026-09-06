<?php

use App\Policies\PermissionPolicy;
use Spatie\Permission\Models\Permission;

test('permission policy authorizes all actions for admin and denies for regular user', function () {
    $policy = new PermissionPolicy;
    $admin = adminUser();
    $user = regularUser();
    $permission = Permission::firstOrCreate(['name' => 'policy-test-perm', 'guard_name' => 'web']);

    expect($policy->viewAny($admin))->toBeTrue()
        ->and($policy->viewAny($user))->toBeFalse()
        ->and($policy->view($admin, $permission))->toBeTrue()
        ->and($policy->view($user, $permission))->toBeFalse()
        ->and($policy->create($admin))->toBeTrue()
        ->and($policy->create($user))->toBeFalse()
        ->and($policy->update($admin, $permission))->toBeTrue()
        ->and($policy->update($user, $permission))->toBeFalse()
        ->and($policy->delete($admin, $permission))->toBeTrue()
        ->and($policy->delete($user, $permission))->toBeFalse()
        ->and($policy->restore($admin, $permission))->toBeTrue()
        ->and($policy->restore($user, $permission))->toBeFalse()
        ->and($policy->forceDelete($admin, $permission))->toBeTrue()
        ->and($policy->forceDelete($user, $permission))->toBeFalse();
});
