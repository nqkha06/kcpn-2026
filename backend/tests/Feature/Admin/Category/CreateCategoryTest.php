<?php

use App\Models\Category;
use Tests\Support\TestData;
use Tests\Support\TestResponseAssertions;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;
use function Pest\Laravel\postJson;

test('an admin can create a category', function () {
    actingAs(adminUser())
        ->postJson('/api/v1/admin/categories', [
            'name' => 'API Category',
            'color' => '#10B981',
            'description' => 'Created through API tests',
            'status' => 'active',
        ])
        ->assertCreated()
        ->assertJsonPath('data.name', 'API Category');

    assertDatabaseHas('categories', ['name' => 'API Category', 'user_id' => null]);
});

test('a guest cannot create an admin category', function () {
    postJson('/api/v1/admin/categories', [
        'name' => 'Guest Category',
        'color' => '#10B981',
        'status' => 'active',
    ])->assertUnauthorized();

    assertDatabaseMissing('categories', ['name' => 'Guest Category']);
});

test('a regular user cannot create an admin category', function () {
    actingAs(regularUser())
        ->postJson('/api/v1/admin/categories', [
            'name' => 'Unauthorized Category',
            'color' => '#10B981',
            'status' => 'active',
        ])
        ->assertForbidden();
});

test('admin category creation validates required fields', function () {
    actingAs(adminUser())
        ->postJson('/api/v1/admin/categories', [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['name', 'color', 'status']);
});

test('admin category creation validates color and status', function () {
    actingAs(adminUser())
        ->postJson('/api/v1/admin/categories', [
            'name' => 'Invalid Category',
            'color' => 'green',
            'status' => 'archived',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['color', 'status']);
});

test('admin category creation rejects a duplicate global name', function () {
    Category::factory()->create(['name' => 'Food']);

    actingAs(adminUser())
        ->postJson('/api/v1/admin/categories', [
            'name' => 'Food',
            'color' => '#10B981',
            'status' => 'active',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('name');
});

test('an admin can create a global category with the same name as a private category', function () {
    Category::factory()->create([
        'user_id' => regularUser()->id,
        'name' => 'Private Name',
    ]);

    actingAs(adminUser())
        ->postJson('/api/v1/admin/categories', [
            'name' => 'Private Name',
            'color' => '#10B981',
            'status' => 'active',
        ])
        ->assertCreated();

    assertDatabaseHas('categories', [
        'user_id' => null,
        'name' => 'Private Name',
    ]);
});

test('admin category create follows shared execution data', function (array $case) {
    if (in_array('duplicate_global_category_exists', $case['preconditions'], true)) {
        Category::factory()->create(['name' => 'Duplicate Global Category', 'user_id' => null]);
    }

    if ($case['actor'] === 'admin') {
        $this->actingAs(adminUser());
    } elseif ($case['actor'] === 'user') {
        $this->actingAs(regularUser());
    }

    $beforeCount = Category::query()->count();
    $response = $this->json(
        $case['request']['method'],
        $case['request']['endpoint'],
        $case['request']['body'],
        $case['request']['headers'],
    );

    TestResponseAssertions::assertForCase($response, $case);

    $expectedDelta = $case['expected']['database_change']['operation'] === 'insert' ? 1 : 0;
    expect(Category::query()->count())->toBe($beforeCount + $expectedDelta);

    if ($expectedDelta === 1) {
        $category = Category::query()->findOrFail($response->json('data.id'));

        expect($category->user_id)->toBeNull()
            ->and($category->expenseTransactions()->count())->toBe(0)
            ->and($category->budgets()->count())->toBe(0);
    }
})->with(TestData::load('admin/categories/create.json'));
