<?php

use Tests\Support\TestData;
use Tests\Support\TestResponseAssertions;

use function Pest\Laravel\actingAs;

test('account retrieval follows the shared test data contract', function (array $case) {
    $user = null;

    if ($case['actor'] === 'user') {
        $user = regularUser();
        actingAs($user);
    }

    $request = $case['request'];
    $response = $this->getJson($request['endpoint'], $request['headers']);

    TestResponseAssertions::assertForCase($response, $case);

    if ($user !== null) {
        $response->assertJsonPath('data.user.email', $user->email);
    }
})->with(TestData::load('auth/me.json'));
