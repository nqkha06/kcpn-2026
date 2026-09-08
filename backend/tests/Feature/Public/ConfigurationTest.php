<?php

use App\Models\Menu;
use App\Models\Setting;
use App\Services\PublicSiteService;
use Illuminate\Support\Facades\Schema;
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

test('public configuration resolves empty remote and local appearance paths', function () {
    foreach ([
        'appearance.logo_light' => '',
        'appearance.logo_dark' => 'http://cdn.example.test/dark.svg',
        'appearance.favicon' => 'https://cdn.example.test/favicon.ico',
        'appearance.social_image' => 'images/social.png',
    ] as $key => $value) {
        Setting::query()->create(compact('key', 'value'));
    }

    $this->getJson('/api/v1/public/configuration')
        ->assertOk()
        ->assertJsonPath('data.appearance.logo_light', null)
        ->assertJsonPath('data.appearance.logo_dark', 'http://cdn.example.test/dark.svg')
        ->assertJsonPath('data.appearance.favicon', 'https://cdn.example.test/favicon.ico')
        ->assertJsonPath('data.appearance.social_image', asset('images/social.png'));
});

test('public configuration returns defaults before settings and menu tables exist', function () {
    Schema::shouldReceive('hasTable')->once()->with('settings')->andReturnFalse();
    Schema::shouldReceive('hasTable')->once()->with('menus')->andReturnFalse();

    $configuration = app(PublicSiteService::class)->configuration('en');

    expect($configuration['appearance']['logo_light'])->toBeNull()
        ->and($configuration['menus'])->toBe([
            'home.header' => [],
            'home.footer' => [],
            'user.header' => [],
        ]);
});
