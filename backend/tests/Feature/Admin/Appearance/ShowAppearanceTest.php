<?php

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
