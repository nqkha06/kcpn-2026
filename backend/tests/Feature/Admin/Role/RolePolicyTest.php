<?php

use App\Policies\RolePolicy;
use Spatie\Permission\Models\Role;

test('role policy authorizes all actions for admin and denies for regular user', function () {
    $policy = new RolePolicy;
    $admin = adminUser();
    $user = regularUser();
    $role = Role::firstOrCreate(['name' => 'policy-test-role', 'guard_name' => 'web']);

    expect($policy->viewAny($admin))->toBeTrue()
        ->and($policy->viewAny($user))->toBeFalse()
        ->and($policy->view($admin, $role))->toBeTrue()
        ->and($policy->view($user, $role))->toBeFalse()
        ->and($policy->create($admin))->toBeTrue()
        ->and($policy->create($user))->toBeFalse()
        ->and($policy->update($admin, $role))->toBeTrue()
        ->and($policy->update($user, $role))->toBeFalse()
        ->and($policy->delete($admin, $role))->toBeTrue()
        ->and($policy->delete($user, $role))->toBeFalse()
        ->and($policy->restore($admin, $role))->toBeTrue()
        ->and($policy->restore($user, $role))->toBeFalse()
        ->and($policy->forceDelete($admin, $role))->toBeTrue()
        ->and($policy->forceDelete($user, $role))->toBeFalse();
});

test('role policy protects system admin and super-admin roles from deletion', function () {
    $policy = new RolePolicy;
    $admin = adminUser();
    $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
    $superAdminRole = Role::firstOrCreate(['name' => 'super-admin', 'guard_name' => 'web']);

    expect($policy->delete($admin, $adminRole))->toBeFalse()
        ->and($policy->delete($admin, $superAdminRole))->toBeFalse();
});
