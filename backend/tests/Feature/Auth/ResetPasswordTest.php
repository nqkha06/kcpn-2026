<?php

use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;

use function Pest\Laravel\postJson;

test('the reset password API updates the password', function () {
    $user = User::factory()->create(['email' => 'reset@example.com']);
    $token = Password::broker()->createToken($user);

    postJson('/api/v1/auth/reset-password', [
        'token' => $token,
        'email' => $user->email,
        'password' => 'new-password',
        'password_confirmation' => 'new-password',
    ])->assertOk();

    expect(Hash::check('new-password', $user->fresh()->password))->toBeTrue();
});

test('reset password rejects an invalid token', function () {
    $user = User::factory()->create(['email' => 'invalid-token@example.com']);

    postJson('/api/v1/auth/reset-password', [
        'token' => 'invalid-token',
        'email' => $user->email,
        'password' => 'new-password',
        'password_confirmation' => 'new-password',
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('email');

    expect(Hash::check('new-password', $user->fresh()->password))->toBeFalse();
});

test('reset password validates the password confirmation', function () {
    $user = User::factory()->create(['email' => 'confirmation@example.com']);
    $token = Password::broker()->createToken($user);

    postJson('/api/v1/auth/reset-password', [
        'token' => $token,
        'email' => $user->email,
        'password' => 'new-password',
        'password_confirmation' => 'different-password',
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('password');
});

test('a password reset token cannot be reused', function () {
    $user = User::factory()->create(['email' => 'one-time@example.com']);
    $token = Password::broker()->createToken($user);
    $payload = [
        'token' => $token,
        'email' => $user->email,
        'password' => 'new-password',
        'password_confirmation' => 'new-password',
    ];

    postJson('/api/v1/auth/reset-password', $payload)->assertOk();

    postJson('/api/v1/auth/reset-password', $payload)
        ->assertUnprocessable()
        ->assertJsonValidationErrors('email');
});

test('reset password dispatches the password reset event', function () {
    Event::fake([PasswordReset::class]);
    $user = User::factory()->create(['email' => 'reset-event@example.com']);
    $token = Password::broker()->createToken($user);

    postJson('/api/v1/auth/reset-password', [
        'token' => $token,
        'email' => $user->email,
        'password' => 'new-password',
        'password_confirmation' => 'new-password',
    ])->assertOk();

    Event::assertDispatched(
        PasswordReset::class,
        fn (PasswordReset $event): bool => $event->user->is($user),
    );
});
