<?php

use Spatie\Permission\Models\Permission;
use Tests\Support\TestData;
use Tests\Support\TestResponseAssertions;

function executeAdminPermissionDataCase(object $testCase, array $case): void
{
    $permission = Permission::findOrCreate('shared-permission-target', 'web');
    Permission::findOrCreate('shared-duplicate-permission', 'web');

    if (in_array('permission_list', $case['preconditions'], true)) {
        Permission::findOrCreate('shared-match-permission', 'web');
        Permission::findOrCreate('shared-other-permission', 'web');
        Permission::findOrCreate('shared-match-permission-api', 'api');
    }

    $case = TestData::resolveAliases($case, [
        'permission' => ['id' => $permission->id],
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

    $beforeCount = Permission::query()->count();
    $response = $testCase->json($case['request']['method'], $endpoint, $case['request']['body'], $case['request']['headers']);
    TestResponseAssertions::assertForCase($response, $case);

    $operation = $case['expected']['database_change']['operation'];
    $expectedDelta = match ($operation) {
        'insert' => 1,
        'delete' => -1,
        default => 0,
    };
    expect(Permission::query()->count())->toBe($beforeCount + $expectedDelta);

    if ($operation === 'insert') {
        expect(Permission::query()->where('name', $case['request']['body']['name'])->value('guard_name'))->toBe('web');
    }
}

test('admin permission create follows shared execution data', function (array $case): void {
    executeAdminPermissionDataCase($this, $case);
})->with(TestData::load('admin/permissions/create.json'));

test('admin permission index follows shared execution data', function (array $case): void {
    executeAdminPermissionDataCase($this, $case);
})->with(TestData::load('admin/permissions/index.json'));

test('admin permission update follows shared execution data', function (array $case): void {
    executeAdminPermissionDataCase($this, $case);
})->with(TestData::load('admin/permissions/update.json'));

test('admin permission delete follows shared execution data', function (array $case): void {
    executeAdminPermissionDataCase($this, $case);
})->with(TestData::load('admin/permissions/delete.json'));
