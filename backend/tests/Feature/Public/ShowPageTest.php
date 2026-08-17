<?php

use App\Models\Page;
use Tests\Support\TestData;
use Tests\Support\TestResponseAssertions;

test('public page show follows the shared test data contract', function (array $case) {
    $aliases = [];

    if (in_array('published_page_exists', $case['preconditions'], true)) {
        $page = Page::query()->create([
            'user_id' => adminUser()->id,
            'title' => 'Published Page',
            'slug' => 'published-page',
            'status' => 'published',
        ]);
        $aliases['published_page'] = ['slug' => $page->slug];
    }

    if (in_array('draft_page_exists', $case['preconditions'], true)) {
        $page = Page::query()->create([
            'title' => 'Draft Page',
            'slug' => 'draft-page',
            'status' => 'draft',
        ]);
        $aliases['draft_page'] = ['slug' => $page->slug];
    }

    if (in_array('pending_page_exists', $case['preconditions'], true)) {
        $page = Page::query()->create([
            'title' => 'Pending Page',
            'slug' => 'pending-page',
            'status' => 'pending',
        ]);
        $aliases['pending_page'] = ['slug' => $page->slug];
    }

    if (in_array('published_nested_page_exists', $case['preconditions'], true)) {
        $page = Page::query()->create([
            'title' => 'Nested Page',
            'slug' => 'legal/privacy',
            'status' => 'published',
        ]);
        $aliases['nested_page'] = ['slug' => $page->slug];
    }

    $case = TestData::resolveAliases($case, $aliases);
    $request = $case['request'];
    $url = $request['endpoint'];

    foreach ($request['path'] as $name => $value) {
        $url = str_replace('{'.$name.'}', (string) $value, $url);
    }

    $response = $this->getJson($url, $request['headers']);

    TestResponseAssertions::assertForCase($response, $case);

    if ($case['expected']['status'] === 200) {
        $response->assertHeader('Cache-Control', 'max-age=60, public');
    }
})->with(TestData::load('public/pages-show.json'));
