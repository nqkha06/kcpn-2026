<?php

namespace App\Providers;

use App\Models\User;
use App\Policies\PermissionPolicy;
use App\Policies\RolePolicy;
use Carbon\CarbonImmutable;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(Role::class, RolePolicy::class);
        Gate::policy(Permission::class, PermissionPolicy::class);

        $this->configureDefaults();
        $this->configureFrontendUrls();
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }

    /**
     * Configure URLs that are handled by the standalone frontend.
     */
    protected function configureFrontendUrls(): void
    {
        ResetPassword::createUrlUsing(function (User $user, string $token): string {
            $frontendUrl = config('app.frontend_url');

            if (! is_string($frontendUrl) || $frontendUrl === '') {
                return route('password.reset', [
                    'token' => $token,
                    'email' => $user->getEmailForPasswordReset(),
                ]);
            }

            return sprintf(
                '%s/reset-password?token=%s&email=%s',
                rtrim($frontendUrl, '/'),
                urlencode($token),
                urlencode($user->getEmailForPasswordReset()),
            );
        });
    }
}
