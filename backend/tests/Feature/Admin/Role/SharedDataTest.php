<?php

use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\Support\TestData;
use Tests\Support\TestResponseAssertions;

function executeAdminRoleDataCase(object $testCase, array $case): void
{
    $permission = Permission::findOrCreate('shared-role-permission', 'web');
    $wrongGuardPermission = Permission::findOrCreate('shared-role-api-permission', 'api');
    $role = Role::findOrCreate('shared-role-target', 'web');
    $duplicateRole = Role::findOrCreate('shared-duplicate-role', 'web');
    $adminRole = Role::findOrCreate('admin', 'web');
    $superAdminRole = Role::findOrCreate('super-admin', 'web');

    if (in_array('role_list', $case['preconditions'], true)) {
        Role::findOrCreate('shared-match-role', 'web');
        Role::findOrCreate('shared-other-role', 'web');
        Role::findOrCreate('shared-match-role-api', 'api');
    }

    $case = TestData::resolveAliases($case, [
        'permission' => ['id' => $permission->id],
        'wrong_guard_permission' => ['id' => $wrongGuardPermission->id],
        'role' => ['id' => $role->id],
        'admin_role' => ['id' => $adminRole->id],
        'super_admin_role' => ['id' => $superAdminRole->id],
        'missing' => ['id' => 999_999_999],
    ]);

    if ($case['actor'] === 'admin') {
        $testCase->actingAs(adminUser());
    } elseif ($case['actor'] === 'user') {
        $testCase->actingAs(regularUser());
    }

    $endpoint = $case['request']['endpoint'];
    foreach ($case['request']['path'] as $name => $value) {
        $endpoint = str_replace('{'.$name.'}', (string) $value, $endpoint);
    }
    if ($case['request']['query'] !== []) {
        $endpoint .= '?'.http_build_query($case['request']['query']);
    }

    $beforeCount = Role::query()->count();
    $response = $testCase->json($case['request']['method'], $endpoint, $case['request']['body'], $case['request']['headers']);
    TestResponseAssertions::assertForCase($response, $case);

    $operation = $case['expected']['database_change']['operation'];
    $expectedDelta = match ($operation) {
        'insert' => 1,
        'delete' => -1,
        default => 0,
    };
    expect(Role::query()->count())->toBe($beforeCount + $expectedDelta);

    if ($operation === 'insert' || $operation === 'update') {
        $persistedRole = $operation === 'insert'
            ? Role::query()->where('name', $case['request']['body']['name'])->firstOrFail()
            : $role->fresh();
        expect($persistedRole?->guard_name)->toBe('web');

        if (($case['request']['body']['permissions'] ?? []) === [$permission->id]) {
            expect($persistedRole?->hasPermissionTo($permission))->toBeTrue();
        }
    }
}

test('admin role create follows shared execution data', function (array $case): void {
    executeAdminRoleDataCase($this, $case);
})->with(TestData::load('admin/roles/create.json'));

test('admin role index follows shared execution data', function (array $case): void {
    executeAdminRoleDataCase($this, $case);
})->with(TestData::load('admin/roles/index.json'));

test('admin role update follows shared execution data', function (array $case): void {
    executeAdminRoleDataCase($this, $case);
})->with(TestData::load('admin/roles/update.json'));

test('admin role delete follows shared execution data', function (array $case): void {
    executeAdminRoleDataCase($this, $case);
})->with(TestData::load('admin/roles/delete.json'));
