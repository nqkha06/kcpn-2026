<?php

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Notification;

use function Pest\Laravel\postJson;

test('the forgot password API sends a reset notification', function () {
    Notification::fake();
    $user = User::factory()->create(['email' => 'forgot@example.com']);

    postJson('/api/v1/auth/forgot-password', ['email' => $user->email])
        ->assertOk()
        ->assertJsonPath('success', true);

    Notification::assertSentTo($user, ResetPassword::class);
});

test('forgot password validates the email field', function () {
    Notification::fake();

    postJson('/api/v1/auth/forgot-password', ['email' => 'invalid-email'])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('email');

    Notification::assertNothingSent();
});

test('forgot password rejects an unknown email without sending a notification', function () {
    Notification::fake();

    postJson('/api/v1/auth/forgot-password', ['email' => 'missing@example.com'])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('email');

    Notification::assertNothingSent();
});

test('forgot password throttles repeated reset requests', function () {
    Notification::fake();
    $user = User::factory()->create(['email' => 'throttled@example.com']);

    postJson('/api/v1/auth/forgot-password', ['email' => $user->email])
        ->assertOk();

    postJson('/api/v1/auth/forgot-password', ['email' => $user->email])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('email');

    Notification::assertSentToTimes($user, ResetPassword::class, 1);
});
