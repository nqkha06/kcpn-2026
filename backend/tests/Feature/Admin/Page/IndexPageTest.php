<?php

use App\Models\Page;
use Tests\Support\TestData;
use Tests\Support\TestResponseAssertions;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\getJson;

test('an admin can list pages', function () {
    Page::query()->create(['title' => 'API Page', 'slug' => 'api-page', 'status' => 'published']);

    actingAs(adminUser())
        ->getJson('/api/v1/admin/pages')
        ->assertOk()
        ->assertJsonPath('meta.total', 1)
        ->assertJsonPath('data.0.slug', 'api-page');
});

test('a guest cannot list admin pages', function () {
    getJson('/api/v1/admin/pages')->assertUnauthorized();
});

test('a regular user cannot list admin pages', function () {
    actingAs(regularUser())
        ->getJson('/api/v1/admin/pages')
        ->assertForbidden();
});

test('an admin can search filter sort and paginate pages', function () {
    $matching = Page::query()->create([
        'title' => 'Alpha Guide',
        'slug' => 'alpha-guide',
        'status' => 'published',
    ]);
    Page::query()->create([
        'title' => 'Zulu Guide',
        'slug' => 'zulu-guide',
        'status' => 'draft',
    ]);

    actingAs(adminUser())
        ->getJson('/api/v1/admin/pages?search=Guide&status=published&sort=title&direction=asc&per_page=1')
        ->assertOk()
        ->assertJsonPath('meta.total', 1)
        ->assertJsonPath('meta.per_page', 1)
        ->assertJsonPath('data.0.id', $matching->id);
});

test('admin page list query parameters are validated', function () {
    actingAs(adminUser())
        ->getJson('/api/v1/admin/pages?status=archived&sort=content&direction=sideways&per_page=101')
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['status', 'sort', 'direction', 'per_page']);
});

test('admin page index follows shared execution data', function (array $case) {
    Page::query()->create([
        'title' => 'Shared Index Page',
        'slug' => 'shared-index-page',
        'status' => 'published',
    ]);

    if ($case['actor'] === 'admin') {
        $this->actingAs(adminUser());
    } elseif ($case['actor'] === 'user') {
        $this->actingAs(regularUser());
    }

    $query = http_build_query($case['request']['query']);
    $endpoint = $case['request']['endpoint'].($query === '' ? '' : '?'.$query);
    $beforeCount = Page::query()->count();
    $response = $this->json('GET', $endpoint, [], $case['request']['headers']);

    TestResponseAssertions::assertForCase($response, $case);
    expect(Page::query()->count())->toBe($beforeCount);
})->with(TestData::load('admin/pages/index.json'));
