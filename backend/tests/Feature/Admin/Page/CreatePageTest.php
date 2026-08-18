<?php

use App\Models\Category;
use App\Models\Page;
use Tests\Support\TestData;
use Tests\Support\TestResponseAssertions;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;
use function Pest\Laravel\postJson;

test('an admin can create a page', function () {
    $admin = adminUser();

    actingAs($admin)
        ->postJson('/api/v1/admin/pages', [
            'title' => 'API Page',
            'content' => '<p>Created through API tests</p>',
            'tags' => 'api, test, api',
            'status' => 'published',
        ])
        ->assertCreated()
        ->assertJsonPath('data.slug', 'api-page')
        ->assertJsonPath('data.author.id', $admin->id)
        ->assertJsonPath('data.tags', ['api', 'test']);

    assertDatabaseHas('pages', ['title' => 'API Page', 'slug' => 'api-page']);
});

test('a guest cannot create an admin page', function () {
    postJson('/api/v1/admin/pages', [
        'title' => 'Guest Page',
        'status' => 'draft',
    ])->assertUnauthorized();

    assertDatabaseMissing('pages', ['title' => 'Guest Page']);
});

test('a regular user cannot create an admin page', function () {
    actingAs(regularUser())
        ->postJson('/api/v1/admin/pages', [
            'title' => 'Unauthorized Page',
            'status' => 'draft',
        ])
        ->assertForbidden();
});

test('page creation requires a title and status', function () {
    actingAs(adminUser())
        ->postJson('/api/v1/admin/pages', [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['title', 'status']);
});

test('page creation validates status and category', function () {
    actingAs(adminUser())
        ->postJson('/api/v1/admin/pages', [
            'title' => 'Invalid Page',
            'status' => 'archived',
            'category_id' => 999999,
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['status', 'category_id']);
});

test('generated page slugs are made unique without changing the title', function () {
    Page::query()->create([
        'title' => 'About Us',
        'slug' => 'about-us',
        'status' => 'published',
    ]);

    actingAs(adminUser())
        ->postJson('/api/v1/admin/pages', [
            'title' => 'About Us',
            'status' => 'draft',
        ])
        ->assertCreated()
        ->assertJsonPath('data.title', 'About Us')
        ->assertJsonPath('data.slug', 'about-us-1');

    assertDatabaseHas('pages', ['title' => 'About Us', 'slug' => 'about-us-1']);
});

test('an explicitly duplicated page slug is rejected', function () {
    Page::query()->create([
        'title' => 'Existing',
        'slug' => 'existing',
        'status' => 'published',
    ]);

    actingAs(adminUser())
        ->postJson('/api/v1/admin/pages', [
            'title' => 'Another Page',
            'slug' => 'existing',
            'status' => 'draft',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('slug');
});

test('admin page create follows shared execution data', function (array $case) {
    if (isset($case['blocked'])) {
        $this->markTestSkipped($case['blocked']['reason']);
    }

    $admin = adminUser();
    $category = Category::factory()->create(['user_id' => null]);
    $existingPage = Page::query()->create([
        'user_id' => $admin->id,
        'title' => 'Existing Data Page',
        'slug' => 'existing-page',
        'status' => 'published',
    ]);

    if (in_array('generated_slug_collision_exists', $case['preconditions'], true)) {
        Page::query()->create([
            'user_id' => $admin->id,
            'title' => 'Collision Page',
            'slug' => 'collision-page',
            'status' => 'published',
        ]);
    }

    $case = TestData::resolveAliases($case, [
        'admin' => ['id' => $admin->id],
        'category' => ['id' => $category->id],
        'existing_page' => ['slug' => $existingPage->slug],
        'missing' => ['id' => 999_999_999],
    ]);

    if ($case['actor'] === 'admin') {
        $this->actingAs($admin);
    } elseif ($case['actor'] === 'user') {
        $this->actingAs(regularUser());
    }

    $beforeCount = Page::query()->count();
    $response = $this->json(
        $case['request']['method'],
        $case['request']['endpoint'],
        $case['request']['body'],
        $case['request']['headers'],
    );

    TestResponseAssertions::assertForCase($response, $case);

    $expectedDelta = $case['expected']['database_change']['operation'] === 'insert' ? 1 : 0;
    expect(Page::query()->count())->toBe($beforeCount + $expectedDelta);

    if ($expectedDelta === 1) {
        $page = Page::query()->with(['user', 'category'])->findOrFail($response->json('data.id'));

        expect($page->user->is($admin))->toBeTrue()
            ->and($page->category->is($category))->toBeTrue()
            ->and($page->slug)->not->toBeEmpty();

        if (str_contains($case['description'], 'empty slug')) {
            expect(Page::query()->where('slug', $page->slug)->count())->toBe(1);
        }
    }
})->with(TestData::load('admin/pages/create.json'));
