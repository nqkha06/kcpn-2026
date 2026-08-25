<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Tests\Support\TestData;
use Tests\Support\TestResponseAssertions;

test('logout follows the shared test data contract', function (array $case) {
    if ($case['actor'] === 'authenticated_user') {
        User::factory()->create(['email' => 'logout@example.com', 'password' => 'password']);

        $this->postJson('/api/v1/auth/login', [
            'email' => 'logout@example.com',
            'password' => 'password',
        ])->assertOk();
    }

    $request = $case['request'];
    $response = $this->postJson($request['endpoint'], $request['body'], $request['headers']);

    TestResponseAssertions::assertForCase($response, $case);

    if ($case['actor'] === 'authenticated_user') {
        Auth::forgetGuards();

        $this->getJson('/api/v1/auth/me')->assertUnauthorized();
    }
})->with(TestData::load('auth/logout.json'));
