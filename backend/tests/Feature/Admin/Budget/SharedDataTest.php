<?php

use App\Models\Budget;
use App\Models\Category;
use App\Models\User;
use Tests\Support\TestData;
use Tests\Support\TestResponseAssertions;

function executeAdminBudgetDataCase(object $testCase, array $case): void
{
    $customer = User::factory()->create(['name' => 'Shared Budget Customer']);
    $otherUser = User::factory()->create();
    $category = Category::factory()->create(['user_id' => null, 'name' => 'Shared Budget Category']);
    $otherCategory = Category::factory()->create(['user_id' => $otherUser->id]);
    $duplicateCategory = Category::factory()->create(['user_id' => $customer->id]);
    $budget = null;

    if (str_contains($case['request']['endpoint'], '{budget}')) {
        $budget = Budget::factory()->for($customer)->for($category)->create([
            'period' => 'monthly',
            'status' => 'active',
            'amount_limit' => 4000,
        ]);
    }

    if (in_array('duplicate_budget', $case['preconditions'], true)) {
        $duplicateTarget = $budget === null ? $category : $duplicateCategory;
        Budget::factory()->for($customer)->for($duplicateTarget)->create([
            'period' => 'monthly',
            'status' => 'active',
        ]);
    }

    if (in_array('list_fixtures', $case['preconditions'], true)) {
        Budget::query()->delete();
        Budget::factory()->for($customer)->for($category)->create([
            'amount_limit' => 100,
            'period' => 'monthly',
            'status' => 'active',
        ]);
        Budget::factory()->count(2)->create([
            'amount_limit' => 300,
            'period' => 'yearly',
            'status' => 'inactive',
        ]);
    }

    $case = TestData::resolveAliases($case, [
        'customer' => ['id' => $customer->id],
        'category' => ['id' => $category->id],
        'other_category' => ['id' => $otherCategory->id],
        'duplicate_category' => ['id' => $duplicateCategory->id],
        'budget' => ['id' => $budget?->id ?? 999_999_999],
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

    $beforeCount = Budget::query()->count();
    $response = $testCase->json($case['request']['method'], $endpoint, $case['request']['body'], $case['request']['headers']);
    TestResponseAssertions::assertForCase($response, $case);

    $operation = $case['expected']['database_change']['operation'];
    $expectedDelta = $operation === 'insert' ? 1 : 0;
    expect(Budget::query()->count())->toBe($beforeCount + $expectedDelta);

    if ($operation === 'update') {
        expect($budget?->fresh())->not->toBeNull();
    }
}

test('admin budget create follows shared execution data', function (array $case): void {
    executeAdminBudgetDataCase($this, $case);
})->with(TestData::load('admin/budgets/create.json'));

test('admin budget index follows shared execution data', function (array $case): void {
    executeAdminBudgetDataCase($this, $case);
})->with(TestData::load('admin/budgets/index.json'));

test('admin budget update follows shared execution data', function (array $case): void {
    executeAdminBudgetDataCase($this, $case);
})->with(TestData::load('admin/budgets/update.json'));
