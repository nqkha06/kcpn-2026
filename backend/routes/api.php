<?php

use App\Http\Controllers\Api\V1\Admin\AppearanceController as AdminAppearanceController;
use App\Http\Controllers\Api\V1\Admin\BudgetController as AdminBudgetController;
use App\Http\Controllers\Api\V1\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Api\V1\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Api\V1\Admin\MenuController as AdminMenuController;
use App\Http\Controllers\Api\V1\Admin\PageController as AdminPageController;
use App\Http\Controllers\Api\V1\Admin\PermissionController as AdminPermissionController;
use App\Http\Controllers\Api\V1\Admin\RoleController as AdminRoleController;
use App\Http\Controllers\Api\V1\Admin\TransactionController as AdminTransactionController;
use App\Http\Controllers\Api\V1\Admin\UserController as AdminUserController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\PublicSiteController;
use App\Http\Controllers\Api\V1\User\BudgetController as UserBudgetController;
use App\Http\Controllers\Api\V1\User\CategoryController as UserCategoryController;
use App\Http\Controllers\Api\V1\User\DashboardController as UserDashboardController;
use App\Http\Controllers\Api\V1\User\SettingsController as UserSettingsController;
use App\Http\Controllers\Api\V1\User\TransactionController as UserTransactionController;
use App\Http\Controllers\Api\V1\User\WalletController as UserWalletController;
use Illuminate\Support\Facades\Route;
use Laravel\Sanctum\Http\Controllers\CsrfCookieController;

Route::prefix('v1')->name('api.v1.')->group(function (): void {
    Route::get('sanctum/csrf-cookie', [CsrfCookieController::class, 'show'])
        ->name('sanctum.csrf-cookie');

    Route::prefix('auth')->name('auth.')->group(function (): void {
        Route::post('login', [AuthController::class, 'login'])->name('login');
        Route::post('register', [AuthController::class, 'register'])->name('register');
        Route::post('two-factor-challenge', [AuthController::class, 'twoFactorChallenge'])
            ->name('two-factor-challenge');
        Route::post('forgot-password', [AuthController::class, 'forgotPassword'])
            ->name('forgot-password');
        Route::post('reset-password', [AuthController::class, 'resetPassword'])
            ->name('reset-password');

        Route::middleware('auth:sanctum')->group(function (): void {
            Route::post('logout', [AuthController::class, 'logout'])->name('logout');
            Route::get('me', [AuthController::class, 'me'])->name('me');
        });
    });

    Route::prefix('public')->name('public.')->group(function (): void {
        Route::get('configuration', [PublicSiteController::class, 'configuration'])
            ->name('configuration');
        Route::get('pages/{slug}', [PublicSiteController::class, 'page'])
            ->where('slug', '.*')
            ->name('pages.show');
    });

    Route::middleware(['auth:sanctum', 'role:user|admin'])
        ->prefix('user')
        ->name('user.')
        ->group(function (): void {
            Route::get('dashboard', [UserDashboardController::class, 'show'])->name('dashboard');
            Route::apiResource('categories', UserCategoryController::class);

            Route::get('wallets', [UserWalletController::class, 'index'])->name('wallets.index');
            Route::post('wallets', [UserWalletController::class, 'store'])->name('wallets.store');
            Route::match(['put', 'patch'], 'wallets/{wallet}', [UserWalletController::class, 'update'])
                ->name('wallets.update');
            Route::delete('wallets/{wallet}', [UserWalletController::class, 'destroy'])
                ->name('wallets.destroy');

            Route::get('transactions', [UserTransactionController::class, 'index'])
                ->name('transactions.index');
            Route::post('transactions', [UserTransactionController::class, 'store'])
                ->name('transactions.store');

            Route::get('budgets', [UserBudgetController::class, 'index'])->name('budgets.index');
            Route::post('budgets', [UserBudgetController::class, 'store'])->name('budgets.store');

            Route::middleware('role:user')->prefix('settings')->name('settings.')->group(function (): void {
                Route::get('/', [UserSettingsController::class, 'show'])->name('show');
                Route::patch('profile', [UserSettingsController::class, 'updateProfile'])
                    ->name('profile.update');
                Route::patch('preferences', [UserSettingsController::class, 'updatePreferences'])
                    ->name('preferences.update');
            });
        });

    Route::middleware(['auth:sanctum', 'role:admin'])
        ->prefix('admin')
        ->name('admin.')
        ->group(function (): void {
            Route::get('dashboard', AdminDashboardController::class)->name('dashboard');
            Route::get('appearance', [AdminAppearanceController::class, 'show'])->name('appearance.show');
            Route::post('appearance', [AdminAppearanceController::class, 'update'])->name('appearance.update');
            Route::get('budgets/options', [AdminBudgetController::class, 'options'])
                ->name('budgets.options');
            Route::get('transactions/options', [AdminTransactionController::class, 'options'])
                ->name('transactions.options');
            Route::get('menus/parent-options', [AdminMenuController::class, 'parentOptions'])
                ->name('menus.parent-options');
            Route::get('roles/options', [AdminRoleController::class, 'options'])
                ->name('roles.options');
            Route::get('permissions/options', [AdminPermissionController::class, 'options'])
                ->name('permissions.options');

            Route::apiResources([
                'pages' => AdminPageController::class,
                'categories' => AdminCategoryController::class,
                'menus' => AdminMenuController::class,
                'users' => AdminUserController::class,
                'roles' => AdminRoleController::class,
                'permissions' => AdminPermissionController::class,
                'budgets' => AdminBudgetController::class,
                'transactions' => AdminTransactionController::class,
            ]);
        });
});
