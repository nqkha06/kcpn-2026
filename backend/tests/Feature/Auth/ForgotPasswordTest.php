<?php

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Notification;
use Tests\Support\TestData;
use Tests\Support\TestResponseAssertions;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;

test('forgot password follows the shared test data contract', function (array $case) {
    Notification::fake();
    $user = null;

    if (in_array('user_exists', $case['preconditions'], true)
        || in_array('reset_link_already_requested', $case['preconditions'], true)) {
        $user = User::factory()->create(['email' => 'forgot@example.com']);
    }

    $case = TestData::resolveAliases($case, [
        'user' => ['email' => $user?->email],
    ]);
    $request = $case['request'];

    if (in_array('reset_link_already_requested', $case['preconditions'], true)) {
        $this->postJson($request['endpoint'], $request['body'], $request['headers'])
            ->assertOk();
    }

    $response = $this->postJson($request['endpoint'], $request['body'], $request['headers']);

    TestResponseAssertions::assertForCase($response, $case);

    if ($case['case_id'] === 'AUTH-FORGOT-SEND-EP-001') {
        Notification::assertSentTo($user, ResetPassword::class);
        assertDatabaseHas('password_reset_tokens', ['email' => $user?->email]);
    } elseif ($case['case_id'] === 'AUTH-FORGOT-SEND-BUS-005') {
        Notification::assertSentToTimes($user, ResetPassword::class, 1);
    } else {
        Notification::assertNothingSent();

        if (is_string($request['body']['email'] ?? null)) {
            assertDatabaseMissing('password_reset_tokens', [
                'email' => $request['body']['email'],
            ]);
        }
    }
})->with(TestData::load('auth/forgot-password.json'));
