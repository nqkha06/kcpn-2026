<?php

use App\Models\Category;
use App\Models\Page;
use Tests\Support\TestData;
use Tests\Support\TestResponseAssertions;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\patchJson;

test('an admin can update a page', function () {
    $page = Page::query()->create(['title' => 'Old Page', 'slug' => 'old-page', 'status' => 'published']);

    actingAs(adminUser())
        ->patchJson("/api/v1/admin/pages/{$page->id}", [
            'title' => 'Updated Page',
            'slug' => 'updated-page',
            'tags' => ['api', 'updated'],
            'status' => 'draft',
        ])
        ->assertOk()
        ->assertJsonPath('data.slug', 'updated-page')
        ->assertJsonPath('data.status', 'draft');

    assertDatabaseHas('pages', ['id' => $page->id, 'title' => 'Updated Page']);
});

test('a guest cannot update an admin page', function () {
    $page = Page::query()->create(['title' => 'Page', 'slug' => 'page', 'status' => 'draft']);

    patchJson("/api/v1/admin/pages/{$page->id}", [
        'title' => 'Guest Update',
        'status' => 'draft',
    ])->assertUnauthorized();
});

test('a regular user cannot update an admin page', function () {
    $page = Page::query()->create(['title' => 'Page', 'slug' => 'page', 'status' => 'draft']);

    actingAs(regularUser())
        ->patchJson("/api/v1/admin/pages/{$page->id}", [
            'title' => 'Unauthorized Update',
            'status' => 'draft',
        ])
        ->assertForbidden();
});

test('page update validates required fields', function () {
    $page = Page::query()->create(['title' => 'Page', 'slug' => 'page', 'status' => 'draft']);

    actingAs(adminUser())
        ->patchJson("/api/v1/admin/pages/{$page->id}", [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['title', 'status']);
});

test('a page can keep its own slug when updated', function () {
    $page = Page::query()->create(['title' => 'Page', 'slug' => 'page', 'status' => 'draft']);

    actingAs(adminUser())
        ->patchJson("/api/v1/admin/pages/{$page->id}", [
            'title' => 'Updated Title',
            'slug' => 'page',
            'status' => 'published',
        ])
        ->assertOk()
        ->assertJsonPath('data.slug', 'page');
});

test('page update rejects another pages slug', function () {
    Page::query()->create(['title' => 'Existing', 'slug' => 'existing', 'status' => 'draft']);
    $page = Page::query()->create(['title' => 'Page', 'slug' => 'page', 'status' => 'draft']);

    actingAs(adminUser())
        ->patchJson("/api/v1/admin/pages/{$page->id}", [
            'title' => 'Page',
            'slug' => 'existing',
            'status' => 'draft',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('slug');

    expect($page->fresh()->slug)->toBe('page');
});

test('updating a missing admin page returns not found', function () {
    actingAs(adminUser())
        ->patchJson('/api/v1/admin/pages/999999', [
            'title' => 'Missing',
            'status' => 'draft',
        ])
        ->assertNotFound();
});

test('admin page update follows shared execution data', function (array $case) {
    $admin = adminUser();
    $category = Category::factory()->create(['user_id' => null]);
    $page = Page::query()->create([
        'user_id' => $admin->id,
        'category_id' => $category->id,
        'title' => 'Managed Data Page',
        'slug' => 'managed-data-page',
        'status' => 'draft',
    ]);
    $existingPage = Page::query()->create([
        'user_id' => $admin->id,
        'title' => 'Existing Update Page',
        'slug' => 'existing-page',
        'status' => 'published',
    ]);
    $isMissingPage = in_array('missing_page_alias', $case['preconditions'], true);

    $case = TestData::resolveAliases($case, [
        'page' => ['id' => $isMissingPage ? 999_999_999 : $page->id],
        'category' => ['id' => $category->id],
        'existing_page' => ['slug' => $existingPage->slug],
        'missing' => ['id' => 999_999_999],
    ]);

    if ($case['actor'] === 'admin') {
        $this->actingAs($admin);
    } elseif ($case['actor'] === 'user') {
        $this->actingAs(regularUser());
    }

    $endpoint = $case['request']['endpoint'];
    foreach ($case['request']['path'] as $name => $value) {
        $endpoint = str_replace('{'.$name.'}', (string) $value, $endpoint);
    }

    $original = $page->only(['user_id', 'category_id', 'title', 'slug', 'image', 'tags', 'status']);
    $response = $this->json(
        $case['request']['method'],
        $endpoint,
        $case['request']['body'],
        $case['request']['headers'],
    );

    TestResponseAssertions::assertForCase($response, $case);

    if ($case['expected']['database_change']['operation'] === 'update') {
        $updated = $page->fresh()->load(['user', 'category']);

        expect($updated->user->is($admin))->toBeTrue()
            ->and($updated->category->is($category))->toBeTrue();
    } else {
        expect($page->fresh()->only(array_keys($original)))->toBe($original);
    }
})->with(TestData::load('admin/pages/update.json'));
