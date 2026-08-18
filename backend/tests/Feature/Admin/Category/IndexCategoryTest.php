<?php

use App\Models\Category;
use Tests\Support\TestData;
use Tests\Support\TestResponseAssertions;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\getJson;

test('an admin can list categories', function () {
    $category = Category::factory()->create();

    actingAs(adminUser())
        ->getJson('/api/v1/admin/categories')
        ->assertOk()
        ->assertJsonPath('meta.total', 1)
        ->assertJsonPath('data.0.id', $category->id);
});

test('a guest cannot list admin categories', function () {
    getJson('/api/v1/admin/categories')->assertUnauthorized();
});

test('a regular user cannot list admin categories', function () {
    actingAs(regularUser())
        ->getJson('/api/v1/admin/categories')
        ->assertForbidden();
});

test('admin category list excludes private categories', function () {
    $global = Category::factory()->create();
    Category::factory()->create(['user_id' => regularUser()->id]);

    actingAs(adminUser())
        ->getJson('/api/v1/admin/categories')
        ->assertOk()
        ->assertJsonPath('meta.total', 1)
        ->assertJsonPath('data.0.id', $global->id);
});

test('an admin can search filter sort and paginate categories', function () {
    $matching = Category::factory()->create([
        'name' => 'Alpha Food',
        'status' => 'active',
    ]);
    Category::factory()->create(['name' => 'Zulu Food', 'status' => 'inactive']);

    actingAs(adminUser())
        ->getJson('/api/v1/admin/categories?search=Food&status=active&sort=name&direction=asc&per_page=1')
        ->assertOk()
        ->assertJsonPath('meta.total', 1)
        ->assertJsonPath('data.0.id', $matching->id);
});

test('admin category list query parameters are validated', function () {
    actingAs(adminUser())
        ->getJson('/api/v1/admin/categories?status=archived&sort=color&direction=sideways&per_page=101')
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['status', 'sort', 'direction', 'per_page']);
});

test('admin category index follows shared execution data', function (array $case) {
    Category::factory()->create(['user_id' => null, 'status' => 'active']);
    Category::factory()->create(['user_id' => regularUser()->id, 'status' => 'active']);

    if ($case['actor'] === 'admin') {
        $this->actingAs(adminUser());
    } elseif ($case['actor'] === 'user') {
        $this->actingAs(regularUser());
    }

    $query = http_build_query($case['request']['query']);
    $endpoint = $case['request']['endpoint'].($query === '' ? '' : '?'.$query);
    $beforeCount = Category::query()->count();
    $response = $this->json('GET', $endpoint, [], $case['request']['headers']);

    TestResponseAssertions::assertForCase($response, $case);
    expect(Category::query()->count())->toBe($beforeCount);
})->with(TestData::load('admin/categories/index.json'));
