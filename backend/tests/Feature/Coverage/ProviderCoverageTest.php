<?php

use App\Providers\AppServiceProvider;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\Rules\Password;

test('production defaults configure the complete password rule', function () {
    $originalEnvironment = app()->environment();

    app()->instance('env', 'production');

    try {
        (new AppServiceProvider(app()))->boot();

        expect(Password::default())->toBeInstanceOf(Password::class);
    } finally {
        app()->instance('env', $originalEnvironment);
        DB::prohibitDestructiveCommands(false);
        (new AppServiceProvider(app()))->boot();
    }
});

test('password reset URL uses the configured frontend and application fallback', function () {
    $user = regularUser();
    $callback = ResetPassword::$createUrlCallback;

    expect($callback)->toBeCallable();

    config()->set('app.frontend_url', 'https://frontend.example.test/');
    $frontendUrl = $callback($user, 'token with spaces');

    config()->set('app.frontend_url', null);
    config()->set('app.url', 'https://api.example.test/');
    $fallbackUrl = $callback($user, 'fallback token');

    expect($frontendUrl)->toBe(
        'https://frontend.example.test/reset-password?token=token+with+spaces&email='.urlencode($user->email),
    )->and($fallbackUrl)->toBe(
        'https://api.example.test/reset-password?token=fallback+token&email='.urlencode($user->email),
    );
});

test('Fortify rate limiter callbacks build their runtime keys', function () {
    $twoFactorRequest = Request::create('/two-factor-challenge', 'POST');
    $twoFactorRequest->setLaravelSession(app('session')->driver());
    $twoFactorRequest->session()->put('login.id', 42);

    $loginRequest = Request::create('/login', 'POST', ['email' => 'USER@Example.COM']);
    $loginRequest->server->set('REMOTE_ADDR', '127.0.0.1');

    $twoFactorLimit = RateLimiter::limiter('two-factor')($twoFactorRequest);
    $loginLimit = RateLimiter::limiter('login')($loginRequest);

    expect($twoFactorLimit->maxAttempts)->toBe(5)
        ->and($twoFactorLimit->key)->toBe(42)
        ->and($loginLimit->maxAttempts)->toBe(5)
        ->and($loginLimit->key)->toBe('user@example.com|127.0.0.1');
});
