<?php

use App\Models\Category;
use Tests\Support\TestData;
use Tests\Support\TestResponseAssertions;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\patchJson;

test('an admin can update a category', function () {
    $category = Category::factory()->create();

    actingAs(adminUser())
        ->patchJson("/api/v1/admin/categories/{$category->id}", [
            'name' => 'Updated Category',
            'color' => '#3B82F6',
            'description' => 'Updated',
            'status' => 'inactive',
        ])
        ->assertOk()
        ->assertJsonPath('data.status', 'inactive');

    assertDatabaseHas('categories', ['id' => $category->id, 'name' => 'Updated Category']);
});

test('a guest cannot update an admin category', function () {
    $category = Category::factory()->create();

    patchJson("/api/v1/admin/categories/{$category->id}", [
        'name' => 'Guest Update',
        'color' => '#3B82F6',
        'status' => 'active',
    ])->assertUnauthorized();
});

test('a regular user cannot update an admin category', function () {
    $category = Category::factory()->create();

    actingAs(regularUser())
        ->patchJson("/api/v1/admin/categories/{$category->id}", [
            'name' => 'Unauthorized Update',
            'color' => '#3B82F6',
            'status' => 'active',
        ])
        ->assertForbidden();
});

test('admin category update validates required fields', function () {
    $category = Category::factory()->create();

    actingAs(adminUser())
        ->patchJson("/api/v1/admin/categories/{$category->id}", [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['name', 'color', 'status']);
});

test('admin category update rejects a duplicate global name', function () {
    Category::factory()->create(['name' => 'Food']);
    $category = Category::factory()->create(['name' => 'Travel']);

    actingAs(adminUser())
        ->patchJson("/api/v1/admin/categories/{$category->id}", [
            'name' => 'Food',
            'color' => '#3B82F6',
            'status' => 'active',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('name');

    expect($category->fresh()->name)->toBe('Travel');
});

test('updating a missing admin category returns not found', function () {
    actingAs(adminUser())
        ->patchJson('/api/v1/admin/categories/999999', [
            'name' => 'Missing Category',
            'color' => '#3B82F6',
            'status' => 'active',
        ])
        ->assertNotFound();
});

test('admin category update follows shared execution data', function (array $case) {
    $category = Category::factory()->create([
        'user_id' => null,
        'name' => 'Managed Global Category',
        'status' => 'active',
    ]);

    if (in_array('duplicate_global_category_exists', $case['preconditions'], true)) {
        Category::factory()->create(['name' => 'Duplicate Global Category', 'user_id' => null]);
    }

    $isMissingCategory = in_array('missing_category_alias', $case['preconditions'], true);
    $case = TestData::resolveAliases($case, [
        'category' => ['id' => $isMissingCategory ? 999_999_999 : $category->id],
    ]);

    if ($case['actor'] === 'admin') {
        $this->actingAs(adminUser());
    } elseif ($case['actor'] === 'user') {
        $this->actingAs(regularUser());
    }

    $endpoint = $case['request']['endpoint'];
    foreach ($case['request']['path'] as $name => $value) {
        $endpoint = str_replace('{'.$name.'}', (string) $value, $endpoint);
    }

    $original = $category->only(['user_id', 'name', 'color', 'description', 'status']);
    $response = $this->json(
        $case['request']['method'],
        $endpoint,
        $case['request']['body'],
        $case['request']['headers'],
    );

    TestResponseAssertions::assertForCase($response, $case);

    if ($case['expected']['database_change']['operation'] === 'update') {
        $updated = $category->fresh();

        expect($updated->user_id)->toBeNull()
            ->and($updated->expenseTransactions()->count())->toBe(0)
            ->and($updated->budgets()->count())->toBe(0);
    } else {
        expect($category->fresh()->only(array_keys($original)))->toBe($original);
    }
})->with(TestData::load('admin/categories/update.json'));
