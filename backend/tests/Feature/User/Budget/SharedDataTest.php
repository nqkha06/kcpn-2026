<?php

use App\Models\Budget;
use App\Models\Category;
use App\Models\User;
use Tests\Support\TestData;
use Tests\Support\TestResponseAssertions;

test('user budget create follows shared execution data', function (array $case): void {
    if (($case['blocked'] ?? false) === true) {
        $this->markTestSkipped($case['blocked_reason']);
    }

    $user = regularUser();
    $otherUser = User::factory()->create();
    $category = Category::factory()->create(['user_id' => $user->id, 'status' => 'active']);
    $globalCategory = Category::factory()->create(['user_id' => null, 'status' => 'active']);
    $inactiveCategory = Category::factory()->create(['user_id' => $user->id, 'status' => 'inactive']);
    $otherCategory = Category::factory()->create(['user_id' => $otherUser->id, 'status' => 'active']);

    if (in_array('duplicate_budget', $case['preconditions'], true)) {
        Budget::factory()->for($user)->for($category)->create(['period' => 'monthly']);
    }

    $case = TestData::resolveAliases($case, [
        'user' => ['id' => $user->id],
        'category' => ['id' => $category->id],
        'global_category' => ['id' => $globalCategory->id],
        'inactive_category' => ['id' => $inactiveCategory->id],
        'other_category' => ['id' => $otherCategory->id],
        'missing' => ['id' => 999_999_999],
    ]);

    if ($case['actor'] === 'user') {
        $this->actingAs($user);
    } elseif ($case['actor'] === 'admin') {
        $this->actingAs(adminUser());
    }

    $beforeCount = Budget::query()->count();
    $response = $this->json($case['request']['method'], $case['request']['endpoint'], $case['request']['body'], $case['request']['headers']);
    TestResponseAssertions::assertForCase($response, $case);

    $expectedDelta = $case['expected']['database_change']['operation'] === 'insert' ? 1 : 0;
    expect(Budget::query()->count())->toBe($beforeCount + $expectedDelta);

    if ($expectedDelta === 1) {
        $created = Budget::query()->latest('id')->firstOrFail();
        expect($created->user_id)->toBe($user->id)
            ->and($created->status)->toBe('active');
    }
})->with(TestData::load('user/budgets/create.json'));
