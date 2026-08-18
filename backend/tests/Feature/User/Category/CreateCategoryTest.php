<?php

use App\Models\Category;
use Tests\Support\TestData;
use Tests\Support\TestResponseAssertions;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;
use function Pest\Laravel\postJson;

test('a user can create a private category', function () {
    $user = regularUser();

    actingAs($user)
        ->postJson('/api/v1/user/categories', [
            'name' => 'Private Category',
            'color' => '#10B981',
            'description' => 'Private',
        ])
        ->assertCreated()
        ->assertJsonPath('data.is_private', true);

    assertDatabaseHas('categories', ['user_id' => $user->id, 'name' => 'Private Category']);
});

test('a guest cannot create a private category', function () {
    postJson('/api/v1/user/categories', [
        'name' => 'Guest Category',
        'color' => '#10B981',
    ])->assertUnauthorized();

    assertDatabaseMissing('categories', ['name' => 'Guest Category']);
});

test('category creation validates the name and color', function () {
    actingAs(regularUser())
        ->postJson('/api/v1/user/categories', [
            'name' => '',
            'color' => 'green',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['name', 'color']);
});

test('a user cannot duplicate a visible category name', function () {
    $user = regularUser();
    Category::factory()->create(['name' => 'Food']);

    actingAs($user)
        ->postJson('/api/v1/user/categories', [
            'name' => 'Food',
            'color' => '#10B981',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('name');

    assertDatabaseMissing('categories', [
        'user_id' => $user->id,
        'name' => 'Food',
    ]);
});

test('different users can use the same private category name', function () {
    Category::factory()->create([
        'user_id' => regularUser()->id,
        'name' => 'Side Project',
    ]);
    $user = regularUser();

    actingAs($user)
        ->postJson('/api/v1/user/categories', [
            'name' => 'Side Project',
            'color' => '#10B981',
        ])
        ->assertCreated();

    assertDatabaseHas('categories', [
        'user_id' => $user->id,
        'name' => 'Side Project',
    ]);
});

test('a private category always starts as active', function () {
    $user = regularUser();

    actingAs($user)
        ->postJson('/api/v1/user/categories', [
            'name' => 'Always Active',
            'color' => '#10B981',
            'status' => 'inactive',
        ])
        ->assertCreated()
        ->assertJsonPath('data.status', 'active');

    assertDatabaseHas('categories', [
        'user_id' => $user->id,
        'name' => 'Always Active',
        'status' => 'active',
    ]);
});

test('user category create follows shared execution data', function (array $case) {
    $user = regularUser();

    if (in_array('user_and_global_duplicate_category_exist', $case['preconditions'], true)) {
        Category::factory()->create(['name' => 'Visible Duplicate']);
    }

    if (in_array('other_user_private_category_with_same_name_exists', $case['preconditions'], true)) {
        Category::factory()->create([
            'user_id' => regularUser()->id,
            'name' => 'Other Private Name',
        ]);
    }

    $case = TestData::resolveAliases($case, ['user' => ['id' => $user->id]]);

    if ($case['actor'] === 'user') {
        $this->actingAs($user);
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

        expect($category->user->is($user))->toBeTrue()
            ->and($category->status)->toBe('active');
    }
})->with(TestData::load('user/categories/create.json'));
