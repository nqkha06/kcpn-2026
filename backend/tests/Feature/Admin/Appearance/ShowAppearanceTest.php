<?php

use App\Models\Setting;
use Tests\Support\TestData;
use Tests\Support\TestResponseAssertions;

use function Pest\Laravel\actingAs;

test('appearance retrieval follows the shared test data contract', function (array $case) {
    if ($case['actor'] === 'admin') {
        actingAs(adminUser());
    } elseif ($case['actor'] === 'user') {
        actingAs(regularUser());
    }

    $request = $case['request'];
    $response = $this->getJson($request['endpoint'], $request['headers']);

    TestResponseAssertions::assertForCase($response, $case);

    if ($case['actor'] === 'admin') {
        $response->assertJsonStructure(['data' => ['languages', 'logos', 'general']]);

        $defaultLanguage = collect($response->json('data.languages'))->firstWhere('is_default', true);

        expect($defaultLanguage['code'])->toBe(strtolower((string) config('app.locale')));
    }
})->with(TestData::load('admin/appearance/show.json'));

test('appearance retrieval treats malformed stored general json as empty', function () {
    Setting::query()->create([
        'key' => 'appearance.general',
        'value' => '{invalid-json',
    ]);

    actingAs(adminUser())
        ->getJson('/api/v1/admin/appearance')
        ->assertOk()
        ->assertJsonPath('data.general', []);
});

test('appearance retrieval preserves remote asset URLs', function () {
    Setting::query()->create([
        'key' => 'appearance.logo_light',
        'value' => 'https://cdn.example.test/logo.svg',
    ]);

    actingAs(adminUser())
        ->getJson('/api/v1/admin/appearance')
        ->assertOk()
        ->assertJsonPath('data.logos.logo_light.path', 'https://cdn.example.test/logo.svg')
        ->assertJsonPath('data.logos.logo_light.url', 'https://cdn.example.test/logo.svg');
});
