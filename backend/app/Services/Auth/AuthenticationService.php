<?php

namespace App\Services\Auth;

use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Events\Registered;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Http\JsonResponse;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Actions\AttemptToAuthenticate;
use Laravel\Fortify\Actions\CanonicalizeUsername;
use Laravel\Fortify\Actions\PrepareAuthenticatedSession;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use Laravel\Fortify\Contracts\RedirectsIfTwoFactorAuthenticatable;
use Laravel\Fortify\Contracts\ResetsUserPasswords;
use Laravel\Fortify\Contracts\TwoFactorAuthenticationProvider;
use Laravel\Fortify\Events\TwoFactorAuthenticationFailed;
use Laravel\Fortify\Events\ValidTwoFactorAuthenticationCodeProvided;
use Laravel\Fortify\Fortify;
use Laravel\Fortify\LoginRateLimiter;
use Spatie\Permission\Models\Role;

class AuthenticationService
{
    public function __construct(
        private readonly StatefulGuard $guard,
        private readonly CreatesNewUsers $createsNewUsers,
        private readonly ResetsUserPasswords $resetsUserPasswords,
        private readonly LoginRateLimiter $loginRateLimiter,
        private readonly TwoFactorAuthenticationProvider $twoFactorAuthenticationProvider,
    ) {}

    public function login(\Illuminate\Http\Request $request): LoginResult
    {
        $this->ensureLoginIsNotRateLimited($request);

        $result = (new Pipeline(app()))
            ->send($request)
            ->through(array_filter([
                config('fortify.lowercase_usernames') ? CanonicalizeUsername::class : null,
                RedirectsIfTwoFactorAuthenticatable::class,
                AttemptToAuthenticate::class,
                PrepareAuthenticatedSession::class,
            ]))
            ->then(fn (): mixed => $this->guard->user());

        if ($result instanceof JsonResponse) {
            return new LoginResult(user: null, requiresTwoFactor: true);
        }

        /** @var User $result */
        $this->ensureDefaultRole($result);

        return new LoginResult(
            user: $result->loadMissing('roles.permissions'),
            requiresTwoFactor: false,
        );
    }

    /**
     * @param  array<string, string>  $attributes
     */
    public function register(array $attributes): User
    {
        $user = DB::transaction(function () use ($attributes): User {
            /** @var User $user */
            $user = $this->createsNewUsers->create($attributes);
            $user->assignRole(Role::findOrCreate('user', 'web'));

            return $user;
        });

        event(new Registered($user));
        $this->guard->login($user);

        return $user->loadMissing('roles.permissions');
    }

    public function completeTwoFactorChallenge(\Illuminate\Http\Request $request): User
    {
        $user = User::query()->find($request->session()->get('login.id'));

        if (! $user instanceof User || $user->two_factor_secret === null) {
            throw ValidationException::withMessages([
                'code' => ['The two-factor authentication session has expired. Please sign in again.'],
            ]);
        }

        $isValid = false;

        if ($request->filled('recovery_code')) {
            $recoveryCode = (string) $request->input('recovery_code');
            $validRecoveryCode = collect($user->recoveryCodes())
                ->first(fn (string $code): bool => hash_equals($code, $recoveryCode));

            if (is_string($validRecoveryCode)) {
                $user->replaceRecoveryCode($validRecoveryCode);
                $isValid = true;
            }
        } else {
            $isValid = $this->twoFactorAuthenticationProvider->verify(
                Fortify::currentEncrypter()->decrypt($user->two_factor_secret),
                (string) $request->input('code'),
            );
        }

        if (! $isValid) {
            event(new TwoFactorAuthenticationFailed($user));

            $field = $request->filled('recovery_code') ? 'recovery_code' : 'code';

            throw ValidationException::withMessages([
                $field => ['The provided two-factor authentication code is invalid.'],
            ]);
        }

        event(new ValidTwoFactorAuthenticationCodeProvided($user));

        $remember = (bool) $request->session()->pull('login.remember', false);
        $request->session()->forget('login.id');
        $this->guard->login($user, $remember);
        $request->session()->regenerate();
        $this->ensureDefaultRole($user);

        return $user->loadMissing('roles.permissions');
    }

    public function logout(): void
    {
        $this->guard->logout();
    }

    public function sendPasswordResetLink(string $email): string
    {
        $status = Password::broker(config('fortify.passwords'))
            ->sendResetLink(['email' => Str::lower($email)]);

        if ($status !== Password::RESET_LINK_SENT) {
            throw ValidationException::withMessages([
                'email' => [trans($status)],
            ]);
        }

        return $status;
    }

    /**
     * @param  array<string, string>  $credentials
     */
    public function resetPassword(array $credentials): string
    {
        $status = Password::broker(config('fortify.passwords'))->reset(
            $credentials,
            function (User $user) use ($credentials): void {
                $this->resetsUserPasswords->reset($user, $credentials);
                $user->setRememberToken(Str::random(60));
                $user->save();

                event(new PasswordReset($user));
            },
        );

        if ($status !== Password::PASSWORD_RESET) {
            throw ValidationException::withMessages([
                'email' => [trans($status)],
            ]);
        }

        return $status;
    }

    private function ensureLoginIsNotRateLimited(\Illuminate\Http\Request $request): void
    {
        if (! $this->loginRateLimiter->tooManyAttempts($request)) {
            return;
        }

        $seconds = $this->loginRateLimiter->availableIn($request);

        throw ValidationException::withMessages([
            Fortify::username() => [
                trans('auth.throttle', [
                    'seconds' => $seconds,
                    'minutes' => ceil($seconds / 60),
                ]),
            ],
        ])->status(429);
    }

    private function ensureDefaultRole(User $user): void
    {
        if ($user->roles()->doesntExist()) {
            $user->assignRole(Role::findOrCreate('user', 'web'));
        }
    }
}
