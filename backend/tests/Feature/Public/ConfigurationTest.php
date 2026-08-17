<?php

use App\Models\Menu;
use App\Models\Setting;
use Tests\Support\TestData;
use Tests\Support\TestResponseAssertions;

test('public configuration follows the shared test data contract', function (array $case) {
    $case = TestData::resolveAliases($case, [
        'app' => ['locale' => config('app.locale')],
    ]);

    if (in_array('localized_appearance_exists', $case['preconditions'], true)) {
        Setting::query()->create([
            'key' => 'appearance.general',
            'value' => json_encode([
                'vi' => ['site_name' => 'Hoàn tiền'],
                'en' => ['site_name' => 'Cashback'],
            ], JSON_THROW_ON_ERROR),
        ]);
    }

    if (in_array('active_and_inactive_menus_exist', $case['preconditions'], true)) {
        Menu::factory()->header()->create(['title' => 'Later', 'sort_order' => 2]);
        $first = Menu::factory()->header()->create(['title' => 'First', 'sort_order' => 1]);
        Menu::factory()->header()->create([
            'title' => 'Active Child',
            'parent_id' => $first->id,
            'sort_order' => 2,
        ]);
        Menu::factory()->inactive()->header()->create([
            'title' => 'Inactive Child',
            'parent_id' => $first->id,
            'sort_order' => 1,
        ]);
        $inactiveParent = Menu::factory()->inactive()->header()->create([
            'title' => 'Inactive Parent',
        ]);
        Menu::factory()->header()->create([
            'title' => 'Hidden With Parent',
            'parent_id' => $inactiveParent->id,
        ]);
    }

    $request = $case['request'];
    $query = http_build_query($request['query']);
    $url = $request['endpoint'].($query === '' ? '' : '?'.$query);
    $response = $this->getJson($url, $request['headers']);

    TestResponseAssertions::assertForCase($response, $case);

    if ($case['expected']['status'] === 200) {
        $response->assertHeader('Cache-Control', 'max-age=60, public');
    }

    if (in_array('active_and_inactive_menus_exist', $case['preconditions'], true)) {
        $response
            ->assertJsonMissing(['title' => 'Inactive Child'])
            ->assertJsonMissing(['title' => 'Inactive Parent'])
            ->assertJsonMissing(['title' => 'Hidden With Parent']);
    }
})->with(TestData::load('public/configuration.json'));
