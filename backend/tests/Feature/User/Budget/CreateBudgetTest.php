<?php

use App\Models\Budget;
use App\Models\Category;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\postJson;
use function Pest\Laravel\withHeaders;

beforeEach(function (): void {
    withHeaders([
        'Accept' => 'application/json',
        'Origin' => 'http://localhost:3000',
        'Referer' => 'http://localhost:3000/user/budgets',
    ]);
});

test('guests cannot create a budget', function () {
    $category = Category::factory()->create();

    postJson('/api/v1/user/budgets', [
        'category_id' => $category->id,
        'amount_limit' => 1000,
        'period' => 'monthly',
    ])
        ->assertUnauthorized()
        ->assertJsonPath('message', 'Unauthenticated');
});

test('user can create a budget for their own account', function () {
    $user = regularUser();
    $category = Category::factory()->create();

    $payload = [
        'category_id' => $category->id,
        'amount_limit' => 2500000,
        'period' => 'yearly',
        'note' => '  Annual target  ',
    ];

    $response = actingAs($user, 'web')
        ->postJson('/api/v1/user/budgets', $payload)
        ->assertCreated()
        ->assertJsonPath('message', 'Budget created successfully')
        ->assertJsonPath('data.category_id', $category->id)
        ->assertJsonPath('data.amount_limit', 2500000)
        ->assertJsonPath('data.period', 'yearly')
        ->assertJsonPath('data.note', 'Annual target')
        ->assertJsonPath('data.spent', 0)
        ->assertJsonPath('data.category.id', $category->id);

    assertDatabaseHas('budgets', [
        'id' => $response->json('data.id'),
        'user_id' => $user->id,
        'category_id' => $category->id,
        'amount_limit' => 2500000,
        'period' => 'yearly',
        'status' => 'active',
        'note' => 'Annual target',
    ]);
});

test('created budget always starts as active regardless of input', function () {
    $user = regularUser();
    $category = Category::factory()->create();

    $response = actingAs($user, 'web')
        ->postJson('/api/v1/user/budgets', [
            'category_id' => $category->id,
            'amount_limit' => 100,
            'period' => 'monthly',
            'status' => 'inactive',
        ])
        ->assertCreated()
        ->assertJsonPath('data.status', 'active');

    assertDatabaseHas('budgets', [
        'id' => $response->json('data.id'),
        'status' => 'active',
    ]);
});

test('empty note is stored as null', function () {
    $user = regularUser();
    $category = Category::factory()->create();

    $response = actingAs($user, 'web')
        ->postJson('/api/v1/user/budgets', [
            'category_id' => $category->id,
            'amount_limit' => 100,
            'period' => 'monthly',
            'note' => '   ',
        ])
        ->assertCreated()
        ->assertJsonPath('data.note', null);

    assertDatabaseHas('budgets', [
        'id' => $response->json('data.id'),
        'note' => null,
    ]);
});

test('user can create one budget per category and period', function () {
    $user = regularUser();
    $category = Category::factory()->create();

    $payload = [
        'category_id' => $category->id,
        'amount_limit' => 2500000,
        'period' => 'yearly',
    ];

    actingAs($user, 'web')
        ->postJson('/api/v1/user/budgets', $payload)
        ->assertCreated();

    postJson('/api/v1/user/budgets', $payload)
        ->assertUnprocessable()
        ->assertJsonValidationErrors('category_id');
});

test('user can create the same category with a different period', function () {
    $user = regularUser();
    $category = Category::factory()->create();

    Budget::factory()->for($user)->for($category)->monthly()->create();

    actingAs($user, 'web')
        ->postJson('/api/v1/user/budgets', [
            'category_id' => $category->id,
            'amount_limit' => 100,
            'period' => 'yearly',
        ])
        ->assertCreated();
});

test('budget creation requires category_id amount_limit and period', function () {
    $user = regularUser();

    actingAs($user, 'web')
        ->postJson('/api/v1/user/budgets', [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['category_id', 'amount_limit', 'period']);
});

test('budget creation validates amount_limit range', function () {
    $user = regularUser();
    $category = Category::factory()->create();

    actingAs($user, 'web')
        ->postJson('/api/v1/user/budgets', [
            'category_id' => $category->id,
            'amount_limit' => 0,
            'period' => 'monthly',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['amount_limit']);
});

test('budget creation validates period enum', function () {
    $user = regularUser();
    $category = Category::factory()->create();

    actingAs($user, 'web')
        ->postJson('/api/v1/user/budgets', [
            'category_id' => $category->id,
            'amount_limit' => 100,
            'period' => 'weekly',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['period']);
});

test('budget creation rejects inactive categories', function () {
    $user = regularUser();
    $category = Category::factory()->inactive()->create();

    actingAs($user, 'web')
        ->postJson('/api/v1/user/budgets', [
            'category_id' => $category->id,
            'amount_limit' => 100,
            'period' => 'monthly',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['category_id']);
});

test('budget creation rejects categories owned by another user', function () {
    $user = regularUser();
    $otherUser = User::factory()->create();
    $privateCategory = Category::factory()->create(['user_id' => $otherUser->id]);

    actingAs($user, 'web')
        ->postJson('/api/v1/user/budgets', [
            'category_id' => $privateCategory->id,
            'amount_limit' => 100,
            'period' => 'monthly',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['category_id']);
});

test('budget creation accepts shared categories with no owner', function () {
    $user = regularUser();
    $sharedCategory = Category::factory()->create(['user_id' => null]);

    actingAs($user, 'web')
        ->postJson('/api/v1/user/budgets', [
            'category_id' => $sharedCategory->id,
            'amount_limit' => 100,
            'period' => 'monthly',
        ])
        ->assertCreated();
});

// --- Boundary Value Analysis: amount_limit (rule: numeric, between:0.01,9999999999.99) ---
dataset('amount_limit boundaries', [
    'below min (0.00)' => [0.00, false],
    'just below min (0.009)' => [0.009, false],
    'min boundary (0.01)' => [0.01, true],
    'just above min (0.02)' => [0.02, true],
    'just below max (9999999999.98)' => [9999999999.98, true],
    'max boundary (9999999999.99)' => [9999999999.99, true],
    'just above max (10000000000.00)' => [10000000000.00, false],
    'negative value (-1)' => [-1, false],
]);

test('budget creation enforces amount_limit boundaries', function (float $amount, bool $shouldPass) {
    $user = regularUser();
    $category = Category::factory()->create();

    $response = actingAs($user, 'web')
        ->postJson('/api/v1/user/budgets', [
            'category_id' => $category->id,
            'amount_limit' => $amount,
            'period' => 'monthly',
        ]);

    $shouldPass
        ? $response->assertCreated()
        : $response->assertUnprocessable()->assertJsonValidationErrors(['amount_limit']);
})->with('amount_limit boundaries');

// --- Boundary Value Analysis: period (rule: in:monthly,yearly) ---
dataset('period equivalence classes', [
    'valid monthly' => ['monthly', true],
    'valid yearly' => ['yearly', true],
    'wrong case (Monthly)' => ['Monthly', false],
    'unsupported enum value (weekly)' => ['weekly', false],
    // Laravel's global TrimStrings middleware trims the payload before validation
    // runs, so ' monthly ' becomes 'monthly' and is accepted.
    'padded with whitespace ( monthly )' => [' monthly ', true],
    'empty string' => ['', false],
]);

test('budget creation enforces the period enum strictly', function (string $period, bool $shouldPass) {
    $user = regularUser();
    $category = Category::factory()->create();

    $response = actingAs($user, 'web')
        ->postJson('/api/v1/user/budgets', [
            'category_id' => $category->id,
            'amount_limit' => 100,
            'period' => $period,
        ]);

    $shouldPass
        ? $response->assertCreated()
        : $response->assertUnprocessable()->assertJsonValidationErrors(['period']);
})->with('period equivalence classes');

test('user cannot set status on creation because it is not an accepted field', function () {
    // Decision table note: `status` is silently ignored on the user endpoint (see
    // 'created budget always starts as active regardless of input' above). This test
    // documents that sending status does not produce a validation error either way,
    // i.e. the field is neither validated nor rejected -- it's simply unused.
    $user = regularUser();
    $category = Category::factory()->create();

    actingAs($user, 'web')
        ->postJson('/api/v1/user/budgets', [
            'category_id' => $category->id,
            'amount_limit' => 100,
            'period' => 'monthly',
            'status' => 'not-a-real-status',
        ])
        ->assertCreated()
        ->assertJsonPath('data.status', 'active');
});
