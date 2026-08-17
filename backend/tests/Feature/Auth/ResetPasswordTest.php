<?php

use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Tests\Support\TestData;
use Tests\Support\TestResponseAssertions;

test('reset password follows the shared test data contract', function (array $case) {
    Event::fake([PasswordReset::class]);
    $user = null;
    $token = null;

    if (in_array('user_exists', $case['preconditions'], true)) {
        $user = User::factory()->create([
            'email' => 'reset@example.com',
            'password' => 'password',
        ]);
    }

    if (in_array('valid_reset_token', $case['preconditions'], true)) {
        $token = Password::broker()->createToken($user);
    }

    if (in_array('used_reset_token', $case['preconditions'], true)) {
        $token = Password::broker()->createToken($user);

        $this->postJson('/api/v1/auth/reset-password', [
            'token' => $token,
            'email' => $user?->email,
            'password' => 'used-password',
            'password_confirmation' => 'used-password',
        ])->assertOk();

        Event::fake([PasswordReset::class]);
    }

    $passwordBeforeRequest = $user?->fresh()->password;
    $case = TestData::resolveAliases($case, [
        'user' => ['email' => $user?->email],
        'reset' => ['token' => $token, 'used_token' => $token],
    ]);
    $request = $case['request'];
    $response = $this->postJson($request['endpoint'], $request['body'], $request['headers']);

    TestResponseAssertions::assertForCase($response, $case);

    if ($case['case_id'] === 'AUTH-RESET-UPDATE-EP-001') {
        expect(Hash::check('new-password', $user?->fresh()->password))->toBeTrue();
        Event::assertDispatched(
            PasswordReset::class,
            fn (PasswordReset $event): bool => $event->user->is($user),
        );
    } elseif ($user !== null) {
        expect($user->fresh()->password)->toBe($passwordBeforeRequest);
        Event::assertNotDispatched(PasswordReset::class);
    }
})->with(TestData::load('auth/reset-password.json'));
