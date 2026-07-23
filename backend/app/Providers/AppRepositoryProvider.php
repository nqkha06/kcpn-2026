<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppRepositoryProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public $bindings = [
        'App\Repositories\Contracts\BaseRepositoryInterface' => 'App\Repositories\BaseRepository',

        'App\Repositories\Contracts\UserRepositoryInterface' => 'App\Repositories\UserRepository',

        'App\Repositories\Contracts\PageRepositoryInterface' => 'App\Repositories\PageRepository',

        'App\Repositories\Contracts\CategoryRepositoryInterface' => 'App\Repositories\CategoryRepository',

        'App\Repositories\Contracts\BudgetRepositoryInterface' => 'App\Repositories\BudgetRepository',

        'App\Repositories\Contracts\ExpenseTransactionRepositoryInterface' => 'App\Repositories\ExpenseTransactionRepository',

        'App\Repositories\Contracts\MenuRepositoryInterface' => 'App\Repositories\MenuRepository',
    ];

    public function register(): void
    {
        foreach ($this->bindings as $key => $val) {
            $this->app->bind($key, $val);
        }
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
