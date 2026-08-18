<?php

use App\Models\Menu;
use Tests\Support\TestData;
use Tests\Support\TestResponseAssertions;

function executeAdminMenuDataCase(object $testCase, array $case): void
{
    if (($case['blocked'] ?? false) === true) {
        $testCase->markTestSkipped($case['blocked_reason']);
    }

    $menu = Menu::factory()->header()->create(['title' => 'ZZZ Shared Editable Menu']);
    $parent = Menu::factory()->footer()->create([
        'title' => 'Shared Root',
        'parent_id' => null,
        'status' => 'active',
    ]);

    if (in_array('menu_list', $case['preconditions'], true)) {
        Menu::factory()->footer()->inactive()->create([
            'title' => 'Shared Second Root',
            'parent_id' => null,
        ]);
        Menu::factory()->create([
            'title' => 'Shared Child',
            'parent_id' => $parent->id,
            'canonical' => $parent->canonical,
            'status' => 'active',
        ]);
    }

    $case = TestData::resolveAliases($case, [
        'menu' => ['id' => $menu->id],
        'parent_menu' => ['id' => $parent->id],
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

    $beforeCount = Menu::query()->count();
    $response = $testCase->json($case['request']['method'], $endpoint, $case['request']['body'], $case['request']['headers']);
    TestResponseAssertions::assertForCase($response, $case);

    $operation = $case['expected']['database_change']['operation'];
    expect(Menu::query()->count())->toBe($beforeCount + ($operation === 'insert' ? 1 : 0));

    if ($operation === 'update') {
        expect($menu->fresh())->not->toBeNull();
    }
}

test('admin menu create follows shared execution data', function (array $case): void {
    executeAdminMenuDataCase($this, $case);
})->with(TestData::load('admin/menus/create.json'));

test('admin menu index follows shared execution data', function (array $case): void {
    executeAdminMenuDataCase($this, $case);
})->with(TestData::load('admin/menus/index.json'));

test('admin menu update follows shared execution data', function (array $case): void {
    executeAdminMenuDataCase($this, $case);
})->with(TestData::load('admin/menus/update.json'));

test('admin menu parent options follows shared execution data', function (array $case): void {
    executeAdminMenuDataCase($this, $case);
})->with(TestData::load('admin/menus/parent-options.json'));
