<?php

use App\Enums\BaseStatusEnum;
use App\Models\Budget;
use App\Models\ExpenseTransaction;
use App\Models\Menu;
use App\Models\Setting;
use App\Models\UserMeta;
use App\Models\UserWallet;
use App\Policies\BudgetPolicy;
use App\Policies\ExpenseTransactionPolicy;
use App\Policies\MenuPolicy;
use App\Policies\RolePolicy;
use App\Policies\SettingPolicy;
use App\Policies\UserWalletPolicy;
use App\Support\Authorization\PermissionCatalog;
use Spatie\Permission\Models\Role;

test('base status enum exposes a label and badge for every declared case', function () {
    expect(BaseStatusEnum::PUBLISHED->label())->toBe('Đã xuất bản')
        ->and(BaseStatusEnum::DRAFT->label())->toBe('Bản nháp')
        ->and(BaseStatusEnum::PENDING->label())->toBe('Chờ xử lý')
        ->and(BaseStatusEnum::PUBLISHED->html())->toContain('bg-green')
        ->and(BaseStatusEnum::DRAFT->html())->toContain('bg-blue')
        ->and(BaseStatusEnum::PENDING->html())->toContain('bg-yellow');
});

test('user metadata resolves its owning user', function () {
    $user = regularUser();
    $meta = UserMeta::query()->create([
        'user_id' => $user->id,
        'meta_key' => 'timezone',
        'meta_value' => 'Asia/Ho_Chi_Minh',
    ]);

    expect($meta->user->is($user))->toBeTrue();
});

test('expense transaction restore and force delete policies cover admin owner and stranger paths', function () {
    $admin = adminUser();
    $owner = regularUser();
    $stranger = regularUser();
    $transaction = ExpenseTransaction::factory()->for($owner)->create();
    $policy = new ExpenseTransactionPolicy;

    expect($policy->restore($admin, $transaction))->toBeTrue()
        ->and($policy->restore($owner, $transaction))->toBeTrue()
        ->and($policy->restore($stranger, $transaction))->toBeFalse()
        ->and($policy->forceDelete($admin, $transaction))->toBeTrue()
        ->and($policy->forceDelete($owner, $transaction))->toBeTrue()
        ->and($policy->forceDelete($stranger, $transaction))->toBeFalse();
});

test('budget model abilities cover administrator owner and stranger paths', function () {
    $admin = adminUser();
    $owner = regularUser();
    $stranger = regularUser();
    $budget = Budget::factory()->for($owner)->create();
    $policy = new BudgetPolicy;

    expect($policy->view($admin, $budget))->toBeTrue()
        ->and($policy->view($owner, $budget))->toBeTrue()
        ->and($policy->view($stranger, $budget))->toBeFalse()
        ->and($policy->update($admin, $budget))->toBeTrue()
        ->and($policy->update($owner, $budget))->toBeTrue()
        ->and($policy->update($stranger, $budget))->toBeFalse()
        ->and($policy->delete($admin, $budget))->toBeTrue()
        ->and($policy->delete($owner, $budget))->toBeTrue()
        ->and($policy->delete($stranger, $budget))->toBeFalse();
});

test('expense transaction model abilities cover administrator owner and stranger paths', function () {
    $admin = adminUser();
    $owner = regularUser();
    $stranger = regularUser();
    $transaction = ExpenseTransaction::factory()->for($owner)->create();
    $policy = new ExpenseTransactionPolicy;

    expect($policy->view($admin, $transaction))->toBeTrue()
        ->and($policy->view($owner, $transaction))->toBeTrue()
        ->and($policy->view($stranger, $transaction))->toBeFalse()
        ->and($policy->update($admin, $transaction))->toBeTrue()
        ->and($policy->update($owner, $transaction))->toBeTrue()
        ->and($policy->update($stranger, $transaction))->toBeFalse()
        ->and($policy->delete($admin, $transaction))->toBeTrue()
        ->and($policy->delete($owner, $transaction))->toBeTrue()
        ->and($policy->delete($stranger, $transaction))->toBeFalse();
});

test('role deletion protects built in roles and otherwise requires an administrator', function () {
    $admin = adminUser();
    $user = regularUser();
    $protectedRole = Role::findOrCreate('admin', 'web');
    $protectedSuperAdminRole = Role::findOrCreate('super-admin', 'web');
    $customRole = Role::findOrCreate('coverage-custom-role', 'web');
    $policy = new RolePolicy;

    expect($policy->delete($admin, $protectedRole))->toBeFalse()
        ->and($policy->delete($admin, $protectedSuperAdminRole))->toBeFalse()
        ->and($policy->delete($admin, $customRole))->toBeTrue()
        ->and($policy->delete($user, $customRole))->toBeFalse();
});

test('menu restore and force delete policies allow only administrators', function () {
    $admin = adminUser();
    $user = regularUser();
    $menu = Menu::factory()->create();
    $policy = new MenuPolicy;

    expect($policy->restore($admin, $menu))->toBeTrue()
        ->and($policy->restore($user, $menu))->toBeFalse()
        ->and($policy->forceDelete($admin, $menu))->toBeTrue()
        ->and($policy->forceDelete($user, $menu))->toBeFalse();
});

test('setting policy covers allow and deny results for all model abilities', function () {
    $admin = adminUser();
    $user = regularUser();
    $setting = Setting::query()->create(['key' => 'coverage.setting', 'value' => 'value']);
    $policy = new SettingPolicy;

    expect($policy->viewAny($admin))->toBeTrue()
        ->and($policy->viewAny($user))->toBeFalse()
        ->and($policy->view($admin, $setting))->toBeTrue()
        ->and($policy->view($user, $setting))->toBeFalse()
        ->and($policy->create($admin))->toBeTrue()
        ->and($policy->create($user))->toBeFalse()
        ->and($policy->update($admin, $setting))->toBeTrue()
        ->and($policy->update($user, $setting))->toBeFalse()
        ->and($policy->delete($admin, $setting))->toBeTrue()
        ->and($policy->delete($user, $setting))->toBeFalse();
});

test('wallet policy covers owner and non owner model abilities', function () {
    $owner = regularUser();
    $stranger = regularUser();
    $wallet = UserWallet::factory()->for($owner)->create();
    $policy = new UserWalletPolicy;

    expect($policy->view($owner, $wallet))->toBeTrue()
        ->and($policy->view($stranger, $wallet))->toBeFalse()
        ->and($policy->restore($owner, $wallet))->toBeTrue()
        ->and($policy->restore($stranger, $wallet))->toBeFalse()
        ->and($policy->forceDelete($owner, $wallet))->toBeTrue()
        ->and($policy->forceDelete($stranger, $wallet))->toBeFalse();
});

test('permission catalog covers every role branch and exposes its module map', function () {
    $all = PermissionCatalog::all();
    $admin = PermissionCatalog::forRole(PermissionCatalog::ADMIN_ROLE);
    $user = PermissionCatalog::forRole(PermissionCatalog::USER_ROLE);
    $unknown = PermissionCatalog::forRole('unknown');
    $modules = PermissionCatalog::modules();

    expect($admin)->toBe($all)
        ->and($user)->not->toBeEmpty()
        ->and($user)->each->toStartWith('user.')
        ->and($unknown)->toBe([])
        ->and($modules)->toHaveKeys(['admin.dashboard', 'user.dashboard']);
});
