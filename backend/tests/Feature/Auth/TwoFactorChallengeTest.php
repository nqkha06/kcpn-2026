<?php

use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Event;
use Laravel\Fortify\Contracts\TwoFactorAuthenticationProvider;
use Laravel\Fortify\Events\TwoFactorAuthenticationFailed;
use Laravel\Fortify\Events\ValidTwoFactorAuthenticationCodeProvided;

use function Pest\Laravel\assertAuthenticatedAs;
use function Pest\Laravel\assertGuest;
use function Pest\Laravel\postJson;
use function Pest\Laravel\withHeader;
use function Pest\Laravel\withSession;

test('a valid two factor code completes the login', function () {
    Event::fake([
        Login::class,
        ValidTwoFactorAuthenticationCodeProvided::class,
    ]);

    $user = regularUser();
    $user->forceFill([
        'two_factor_secret' => encrypt('two-factor-secret'),
        'two_factor_confirmed_at' => now(),
    ])->save();

    $provider = mock(TwoFactorAuthenticationProvider::class);
    $provider->shouldReceive('verify')
        ->once()
        ->with('two-factor-secret', '123456')
        ->andReturnTrue();

    app()->instance(TwoFactorAuthenticationProvider::class, $provider);

    withHeader('Origin', 'http://localhost');

    withSession(['login.id' => $user->id])
        ->postJson('/api/v1/auth/two-factor-challenge', ['code' => '123456'])
        ->assertOk()
        ->assertJsonPath('data.requires_two_factor', false)
        ->assertJsonPath('data.user.id', $user->id)
        ->assertSessionMissing('login.id');

    assertAuthenticatedAs($user);
    Event::assertDispatched(ValidTwoFactorAuthenticationCodeProvided::class);
});

test('an invalid two factor code is rejected', function () {
    Event::fake([TwoFactorAuthenticationFailed::class]);

    $user = regularUser();
    $user->forceFill([
        'two_factor_secret' => encrypt('two-factor-secret'),
        'two_factor_confirmed_at' => now(),
    ])->save();

    $provider = mock(TwoFactorAuthenticationProvider::class);
    $provider->shouldReceive('verify')->once()->andReturnFalse();
    app()->instance(TwoFactorAuthenticationProvider::class, $provider);

    withHeader('Origin', 'http://localhost');

    withSession(['login.id' => $user->id])
        ->postJson('/api/v1/auth/two-factor-challenge', ['code' => '000000'])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('code');

    assertGuest();
    Event::assertDispatched(TwoFactorAuthenticationFailed::class);
});

test('a recovery code completes the login and cannot be reused', function () {
    $user = regularUser();
    $user->forceFill([
        'two_factor_secret' => encrypt('two-factor-secret'),
        'two_factor_recovery_codes' => encrypt(json_encode(['recovery-code'])),
        'two_factor_confirmed_at' => now(),
    ])->save();

    withHeader('Origin', 'http://localhost');

    withSession(['login.id' => $user->id])
        ->postJson('/api/v1/auth/two-factor-challenge', [
            'recovery_code' => 'recovery-code',
        ])
        ->assertOk();

    expect($user->fresh()->recoveryCodes())->not->toContain('recovery-code');

    auth()->logout();

    withSession(['login.id' => $user->id])
        ->postJson('/api/v1/auth/two-factor-challenge', [
            'recovery_code' => 'recovery-code',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('recovery_code');
});

test('an expired two factor session is rejected', function () {
    withHeader('Origin', 'http://localhost');

    postJson('/api/v1/auth/two-factor-challenge', ['code' => '123456'])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('code');

    assertGuest();
});

test('a two factor code or recovery code is required', function () {
    postJson('/api/v1/auth/two-factor-challenge', [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['code', 'recovery_code']);
});
