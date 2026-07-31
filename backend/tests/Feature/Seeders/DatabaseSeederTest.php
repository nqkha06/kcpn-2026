<?php

use App\Enums\BaseStatusEnum;
use App\Models\Budget;
use App\Models\Category;
use App\Models\ExpenseTransaction;
use App\Models\Menu;
use App\Models\Page;
use App\Models\Setting;
use App\Models\User;
use App\Models\UserMeta;
use App\Models\UserWallet;
use Illuminate\Support\Facades\Hash;

test('database seeder creates the admin and complete module data', function () {
    $this->seed();

    $admin = User::query()->where('email', 'admin@example.com')->firstOrFail();

    expect(Hash::check('password', $admin->password))->toBeTrue()
        ->and($admin->hasRole('admin'))->toBeTrue()
        ->and($admin->getMeta('currency'))->toBe('VND')
        ->and(Category::query()->whereNull('user_id')->count())->toBe(12)
        ->and(Page::query()->where('status', BaseStatusEnum::PUBLISHED->value)->count())->toBe(4)
        ->and(Menu::query()->count())->toBe(22)
        ->and(Setting::query()->where('key', 'appearance.general')->exists())->toBeTrue()
        ->and(UserWallet::query()->where('user_id', $admin->id)->exists())->toBeTrue()
        ->and(Budget::query()->count())->toBeGreaterThan(0)
        ->and(ExpenseTransaction::query()->count())->toBeGreaterThan(0);

    $this->assertDatabaseHas('pages', [
        'slug' => 'chinh-sach-bao-mat',
        'status' => BaseStatusEnum::PUBLISHED->value,
    ]);
    $this->assertDatabaseHas('menus', [
        'title' => 'Về chúng tôi',
        'url' => '/p/gioi-thieu',
        'status' => 'active',
    ]);
});

test('database seeder can run repeatedly without duplicating records', function () {
    $this->seed();

    $counts = [
        User::class => User::query()->count(),
        UserMeta::class => UserMeta::query()->count(),
        Category::class => Category::query()->count(),
        UserWallet::class => UserWallet::query()->count(),
        Budget::class => Budget::query()->count(),
        ExpenseTransaction::class => ExpenseTransaction::query()->count(),
        Page::class => Page::query()->count(),
        Menu::class => Menu::query()->count(),
        Setting::class => Setting::query()->count(),
    ];

    $this->seed();

    foreach ($counts as $model => $count) {
        expect($model::query()->count())->toBe($count);
    }
});
