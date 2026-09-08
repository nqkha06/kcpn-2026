<?php

use App\Models\Category;
use App\Models\UserWallet;
use App\Services\Admin\AdminBudgetService;
use App\Services\Admin\AdminCategoryService;
use App\Services\Admin\AdminMenuService;
use App\Services\Admin\AdminPageService;
use App\Services\Admin\AdminPermissionService;
use App\Services\Admin\AdminRoleService;
use App\Services\Admin\AdminTransactionService;
use App\Services\Admin\AdminUserService;
use App\Services\User\UserBudgetService;
use App\Services\User\UserTransactionService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

test('service pagination handles an explicitly null search filter', function () {
    $user = regularUser();
    $filters = ['search' => null];
    $paginators = [
        app(AdminBudgetService::class)->paginate($filters),
        app(AdminCategoryService::class)->paginate($filters),
        app(AdminMenuService::class)->paginate($filters),
        app(AdminPageService::class)->paginate($filters),
        app(AdminPermissionService::class)->paginate($filters),
        app(AdminRoleService::class)->paginate($filters),
        app(AdminTransactionService::class)->paginate($filters),
        app(AdminUserService::class)->paginate([...$filters, 'email' => null]),
        app(UserTransactionService::class)->paginate($user, $filters),
    ];

    expect($paginators)->each->toBeInstanceOf(LengthAwarePaginator::class);
});

test('service create methods handle omitted optional note and url values', function () {
    $user = regularUser();
    $category = Category::factory()->create();
    $userBudgetCategory = Category::factory()->create();
    $wallet = UserWallet::factory()->for($user)->create();

    $adminBudget = app(AdminBudgetService::class)->create([
        'user_id' => $user->id,
        'category_id' => $userBudgetCategory->id,
        'amount_limit' => 1000,
        'period' => 'monthly',
        'status' => 'active',
    ]);
    $adminTransaction = app(AdminTransactionService::class)->create([
        'user_id' => $user->id,
        'wallet_id' => $wallet->id,
        'category_id' => $category->id,
        'type' => 'expense',
        'amount' => 100,
        'transacted_at' => now()->toDateString(),
        'status' => 'posted',
    ]);
    $userBudget = app(UserBudgetService::class)->create($user, [
        'category_id' => $category->id,
        'amount_limit' => 1000,
        'period' => 'monthly',
    ]);
    $userTransaction = app(UserTransactionService::class)->create($user, [
        'wallet_id' => $wallet->id,
        'category_id' => $category->id,
        'type' => 'expense',
        'amount' => 100,
        'transacted_at' => now()->toDateString(),
    ]);
    $menu = app(AdminMenuService::class)->create([
        'title' => 'Coverage menu',
        'canonical' => 'home.header',
        'parent_id' => null,
        'target' => '_self',
        'status' => 'active',
    ]);

    expect($adminBudget->note)->toBeNull()
        ->and($adminTransaction->note)->toBeNull()
        ->and($userBudget->note)->toBeNull()
        ->and($userTransaction->note)->toBeNull()
        ->and($menu->url)->toBeNull();
});
