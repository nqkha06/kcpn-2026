<?php

use App\Models\Category;
use Tests\Support\TestData;
use Tests\Support\TestResponseAssertions;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;
use function Pest\Laravel\patchJson;

test('a user can update their private category', function () {
    $user = regularUser();
    $category = Category::factory()->create(['user_id' => $user->id]);

    actingAs($user)
        ->patchJson("/api/v1/user/categories/{$category->id}", [
            'name' => 'Updated Private Category',
            'color' => '#3B82F6',
            'description' => 'Updated',
        ])
        ->assertOk()
        ->assertJsonPath('data.name', 'Updated Private Category');

    assertDatabaseHas('categories', ['id' => $category->id, 'name' => 'Updated Private Category']);
});

test('a guest cannot update a private category', function () {
    $category = Category::factory()->create(['user_id' => regularUser()->id]);

    patchJson("/api/v1/user/categories/{$category->id}", [
        'name' => 'Guest Update',
        'color' => '#3B82F6',
    ])->assertUnauthorized();

    assertDatabaseMissing('categories', [
        'id' => $category->id,
        'name' => 'Guest Update',
    ]);
});

test('a user cannot update another users private category', function () {
    $category = Category::factory()->create(['user_id' => regularUser()->id]);

    actingAs(regularUser())
        ->patchJson("/api/v1/user/categories/{$category->id}", [
            'name' => 'Stolen Category',
            'color' => '#3B82F6',
        ])
        ->assertForbidden();

    assertDatabaseMissing('categories', [
        'id' => $category->id,
        'name' => 'Stolen Category',
    ]);
});

test('a user cannot update a global category', function () {
    $category = Category::factory()->create();

    actingAs(regularUser())
        ->patchJson("/api/v1/user/categories/{$category->id}", [
            'name' => 'Changed Global',
            'color' => '#3B82F6',
        ])
        ->assertForbidden();
});

test('category update validates the name and color', function () {
    $user = regularUser();
    $category = Category::factory()->create(['user_id' => $user->id]);

    actingAs($user)
        ->patchJson("/api/v1/user/categories/{$category->id}", [
            'name' => '',
            'color' => 'blue',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['name', 'color']);
});

test('a private category cannot be renamed to a visible category name', function () {
    $user = regularUser();
    Category::factory()->create(['name' => 'Food']);
    $category = Category::factory()->create([
        'user_id' => $user->id,
        'name' => 'Lunch',
    ]);

    actingAs($user)
        ->patchJson("/api/v1/user/categories/{$category->id}", [
            'name' => 'Food',
            'color' => '#3B82F6',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('name');

    expect($category->fresh()->name)->toBe('Lunch');
});

test('updating a missing category returns not found', function () {
    actingAs(regularUser())
        ->patchJson('/api/v1/user/categories/999999', [
            'name' => 'Missing Category',
            'color' => '#3B82F6',
        ])
        ->assertNotFound();
});

test('user category update follows shared execution data', function (array $case) {
    $user = regularUser();
    $categoryOwner = in_array('user_and_other_category_exists', $case['preconditions'], true)
        ? regularUser()
        : $user;
    $isGlobal = in_array('user_and_global_category_exist', $case['preconditions'], true);
    $isInactive = in_array('user_with_own_inactive_category_exists', $case['preconditions'], true);
    $category = Category::factory()->create([
        'user_id' => $isGlobal ? null : $categoryOwner->id,
        'name' => 'Original Category',
        'status' => $isInactive ? 'inactive' : 'active',
    ]);

    if (in_array('user_with_own_category_and_global_duplicate_exist', $case['preconditions'], true)) {
        Category::factory()->create(['name' => 'Visible Duplicate']);
    }

    if (in_array('user_with_own_category_and_other_private_same_name_exists', $case['preconditions'], true)) {
        Category::factory()->create([
            'user_id' => regularUser()->id,
            'name' => 'Other Private Name',
        ]);
    }

    $isMissingCategory = in_array('missing_category_alias', $case['preconditions'], true);
    $case = TestData::resolveAliases($case, [
        'user' => ['id' => $user->id],
        'category' => ['id' => $isMissingCategory ? 999_999_999 : $category->id],
    ]);

    if ($case['actor'] === 'user') {
        $this->actingAs($user);
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

        expect($updated->user->is($user))->toBeTrue()
            ->and($updated->expenseTransactions()->count())->toBe(0)
            ->and($updated->budgets()->count())->toBe(0);
    } else {
        expect($category->fresh()->only(array_keys($original)))->toBe($original);
    }
})->with(TestData::load('user/categories/update.json'));
