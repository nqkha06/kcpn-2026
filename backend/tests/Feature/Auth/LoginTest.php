<?php

use App\Models\User;
use Tests\Support\TestData;
use Tests\Support\TestResponseAssertions;

use function Pest\Laravel\assertAuthenticatedAs;
use function Pest\Laravel\assertGuest;

test('login follows the shared test data contract', function (array $case) {
    $aliases = [];
    $user = null;

    if (in_array('user_exists', $case['preconditions'], true)) {
        $user = User::factory()->create([
            'email' => 'login@example.com',
            'password' => 'password',
        ]);
        $aliases['user'] = ['email' => $user->email, 'password' => 'password'];
    }

    if (in_array('two_factor_user_exists', $case['preconditions'], true)) {
        $user = User::factory()->withTwoFactor()->create([
            'email' => 'two-factor@example.com',
            'password' => 'password',
        ]);
        $aliases['two_factor_user'] = ['email' => $user->email, 'password' => 'password'];
        $this->withSession([]);
    }

    if (in_array('unconfirmed_two_factor_user_exists', $case['preconditions'], true)) {
        $user = User::factory()->create([
            'email' => 'unconfirmed-two-factor@example.com',
            'password' => 'password',
        ]);
        $user->forceFill([
            'two_factor_secret' => encrypt('two-factor-secret'),
            'two_factor_recovery_codes' => encrypt(json_encode(['recovery-code'], JSON_THROW_ON_ERROR)),
            'two_factor_confirmed_at' => null,
        ])->save();
        $aliases['unconfirmed_two_factor_user'] = ['email' => $user->email, 'password' => 'password'];
    }

    $case = TestData::resolveAliases($case, $aliases);
    $request = $case['request'];
    $response = $this->postJson($request['endpoint'], $request['body'], $request['headers']);

    TestResponseAssertions::assertForCase($response, $case);

    if (($case['expected']['json_paths']['data.requires_two_factor'] ?? null) === false) {
        assertAuthenticatedAs($user);
        $response->assertJsonPath('data.user.id', $user?->id);
    } else {
        assertGuest();
    }
})->with(TestData::load('auth/login.json'));

test('login is rate limited after repeated failed attempts', function () {
    User::factory()->create([
        'email' => 'limited@example.com',
        'password' => 'password',
    ]);

    foreach (range(1, 5) as $attempt) {
        $this->postJson('/api/v1/auth/login', [
            'email' => 'limited@example.com',
            'password' => 'incorrect-password',
        ])->assertUnprocessable();
    }

    $this->postJson('/api/v1/auth/login', [
        'email' => 'limited@example.com',
        'password' => 'incorrect-password',
    ])
        ->assertStatus(429)
        ->assertJsonValidationErrors('email');
});
