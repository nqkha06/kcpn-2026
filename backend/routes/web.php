<?php

use App\Http\Controllers\Admin\Appearance\OptionController;
use App\Http\Controllers\Admin\BudgetController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\MenuController;
use App\Http\Controllers\Admin\PageController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\Settings\LanguageController;
use App\Http\Controllers\Admin\TransactionController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\PublicPageShowController;
use App\Http\Controllers\User\BudgetsController;
use App\Http\Controllers\User\BudgetStoreController;
use App\Http\Controllers\User\CategoryListController;
use App\Http\Controllers\User\DashboardController;
use App\Http\Controllers\User\HomeController;
use App\Http\Controllers\User\TransactionsController;
use App\Http\Controllers\User\TransactionStoreController;
use App\Http\Controllers\User\UserPreferenceController;
use App\Http\Controllers\User\UserProfileController;
use App\Http\Controllers\User\UserSettingsController;
use App\Http\Controllers\User\WalletsController;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

Route::inertia('/', 'Index', [
    'canRegister' => Features::enabled(Features::registration()),
])->name('home');

Route::get('/p/{slug}', PublicPageShowController::class)
    ->where('slug', '.*')
    ->name('pages.show');

Route::prefix('user')->middleware(['auth', 'role:user|admin'])->name('expense.')->group(function () {
    Route::get('/', HomeController::class)->name('home');
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::get('/transactions', TransactionsController::class)->name('transactions');
    Route::post('/transactions', TransactionStoreController::class)->middleware('auth')->name('transactions.store');
    Route::get('/budgets', BudgetsController::class)->name('budgets');
    Route::post('/budgets', BudgetStoreController::class)->middleware('auth')->name('budgets.store');
    Route::get('/wallets', [WalletsController::class, 'index'])->middleware('auth')->name('wallets');
    Route::post('/wallets', [WalletsController::class, 'store'])->middleware('auth')->name('wallets.store');
    Route::put('/wallets/{wallet}', [WalletsController::class, 'update'])->middleware('auth')->name('wallets.update');
    Route::delete('/wallets/{wallet}', [WalletsController::class, 'destroy'])->middleware('auth')->name('wallets.destroy');
    Route::get('/categories', CategoryListController::class)->name('categories.index');
});

Route::middleware(['auth', 'role:user'])->prefix('user')->name('user.')->group(function () {
    Route::get('/settings', UserSettingsController::class)->name('settings');
    Route::patch('/settings/profile', UserProfileController::class)->name('settings.profile.update');
    Route::patch('/settings/preferences', UserPreferenceController::class)->name('settings.preferences.update');
});

Route::middleware(['auth', 'verified', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', AdminDashboardController::class)->name('dashboard');
    Route::resource('pages', PageController::class);
    Route::resource('users', UserController::class);
    Route::resource('categories', CategoryController::class);
    Route::resource('menus', MenuController::class);
    Route::resource('budgets', BudgetController::class);
    Route::resource('transactions', TransactionController::class);
    Route::resource('roles', RoleController::class);
    Route::resource('permissions', PermissionController::class);

    Route::prefix('settings')->name('settings.')->group(function () {
        Route::resource('languages', LanguageController::class);
        Route::get('appearance/options', [OptionController::class, 'index'])->name('appearance.options.index');
        Route::post('appearance/options', [OptionController::class, 'store'])->name('appearance.options.store');
    });
});

require __DIR__.'/settings.php';
