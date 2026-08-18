<?php

use App\Models\Budget;
use App\Models\Category;
use App\Models\ExpenseTransaction;
use Tests\Support\TestData;
use Tests\Support\TestResponseAssertions;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;
use function Pest\Laravel\deleteJson;

test('a user can delete their unused private category', function () {
    $user = regularUser();
    $category = Category::factory()->create(['user_id' => $user->id]);

    actingAs($user)
        ->deleteJson("/api/v1/user/categories/{$category->id}")
        ->assertOk();

    assertDatabaseMissing('categories', ['id' => $category->id]);
});

test('a guest cannot delete a private category', function () {
    $category = Category::factory()->create(['user_id' => regularUser()->id]);

    deleteJson("/api/v1/user/categories/{$category->id}")
        ->assertUnauthorized();

    assertDatabaseHas('categories', ['id' => $category->id]);
});

test('a user cannot delete another users private category', function () {
    $category = Category::factory()->create(['user_id' => regularUser()->id]);

    actingAs(regularUser())
        ->deleteJson("/api/v1/user/categories/{$category->id}")
        ->assertForbidden();

    assertDatabaseHas('categories', ['id' => $category->id]);
});

test('a user cannot delete a global category', function () {
    $category = Category::factory()->create();

    actingAs(regularUser())
        ->deleteJson("/api/v1/user/categories/{$category->id}")
        ->assertForbidden();

    assertDatabaseHas('categories', ['id' => $category->id]);
});

test('a category used by a transaction cannot be deleted', function () {
    $user = regularUser();
    $category = Category::factory()->create(['user_id' => $user->id]);
    ExpenseTransaction::factory()->forUser($user)->create(['category_id' => $category->id]);

    actingAs($user)
        ->deleteJson("/api/v1/user/categories/{$category->id}")
        ->assertStatus(409)
        ->assertJsonValidationErrors('category');

    assertDatabaseHas('categories', ['id' => $category->id]);
});

test('a category used by a budget cannot be deleted', function () {
    $user = regularUser();
    $category = Category::factory()->create(['user_id' => $user->id]);
    Budget::factory()->for($user)->create(['category_id' => $category->id]);

    actingAs($user)
        ->deleteJson("/api/v1/user/categories/{$category->id}")
        ->assertStatus(409)
        ->assertJsonValidationErrors('category');

    assertDatabaseHas('categories', ['id' => $category->id]);
});

test('deleting a missing category returns not found', function () {
    actingAs(regularUser())
        ->deleteJson('/api/v1/user/categories/999999')
        ->assertNotFound();
});

test('user category delete follows shared execution data', function (array $case) {
    $user = regularUser();
    $categoryOwner = in_array('user_and_other_category_exists', $case['preconditions'], true)
        ? regularUser()
        : $user;
    $isGlobal = in_array('user_and_global_category_exist', $case['preconditions'], true);
    $category = Category::factory()->create([
        'user_id' => $isGlobal ? null : $categoryOwner->id,
        'name' => 'Delete Target',
    ]);

    if (in_array('user_category_referenced_by_transaction_exists', $case['preconditions'], true)) {
        ExpenseTransaction::factory()->forUser($user)->create(['category_id' => $category->id]);
    }

    if (in_array('user_category_referenced_by_budget_exists', $case['preconditions'], true)) {
        Budget::factory()->for($user)->create(['category_id' => $category->id]);
    }

    $isMissingCategory = in_array('missing_category_alias', $case['preconditions'], true);
    $case = TestData::resolveAliases($case, [
        'category' => ['id' => $isMissingCategory ? 999_999_999 : $category->id],
    ]);

    if ($case['actor'] === 'user') {
        $this->actingAs($user);
    }

    $endpoint = $case['request']['endpoint'];
    foreach ($case['request']['path'] as $name => $value) {
        $endpoint = str_replace('{'.$name.'}', (string) $value, $endpoint);
    }

    $response = $this->json(
        $case['request']['method'],
        $endpoint,
        $case['request']['body'],
        $case['request']['headers'],
    );

    TestResponseAssertions::assertForCase($response, $case);

    if ($case['expected']['database_change']['operation'] === 'delete') {
        expect(Category::query()->find($category->id))->toBeNull();
    } else {
        expect(Category::query()->find($category->id))->not->toBeNull();
    }
})->with(TestData::load('user/categories/delete.json'));
