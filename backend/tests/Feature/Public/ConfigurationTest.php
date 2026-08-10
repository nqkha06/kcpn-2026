<?php

use App\Models\Menu;
use App\Models\Setting;

use function Pest\Laravel\getJson;

test('the public configuration API returns active menus', function () {
    $menu = Menu::factory()->header()->create(['title' => 'Trang chủ']);
    Menu::factory()->inactive()->header()->create(['title' => 'Ẩn']);

    getJson('/api/v1/public/configuration?locale=vi')
        ->assertOk()
        ->assertJsonPath('data.locale', 'vi')
        ->assertJsonPath('data.menus.home_header.0.id', $menu->id)
        ->assertJsonMissing(['title' => 'Ẩn']);
});

test('public configuration uses the default locale when none is provided', function () {
    getJson('/api/v1/public/configuration')
        ->assertOk()
        ->assertHeader('Cache-Control', 'max-age=60, public')
        ->assertJsonPath('data.locale', config('app.locale'))
        ->assertJsonPath('data.default_locale', config('app.locale'))
        ->assertJsonPath('data.locales', config('app.supported_locales'));
});

test('public configuration rejects unsupported locales', function () {
    getJson('/api/v1/public/configuration?locale=fr')
        ->assertUnprocessable()
        ->assertJsonValidationErrors('locale');
});

test('public configuration returns localized appearance and asset urls', function () {
    Setting::query()->create([
        'key' => 'appearance.general',
        'value' => json_encode([
            'vi' => ['site_name' => 'Hoàn tiền', 'site_title' => 'Quản lý chi tiêu'],
            'en' => ['site_name' => 'Cashback', 'site_title' => 'Expense manager'],
        ]),
    ]);
    Setting::query()->create([
        'key' => 'appearance.logo_light',
        'value' => 'settings/logo.png',
    ]);

    getJson('/api/v1/public/configuration?locale=en')
        ->assertOk()
        ->assertJsonPath('data.appearance.site_name', 'Cashback')
        ->assertJsonPath('data.appearance.site_title', 'Expense manager')
        ->assertJsonPath('data.appearance.logo_light', asset('settings/logo.png'));
});

test('public configuration returns active menu trees in display order', function () {
    $later = Menu::factory()->header()->create(['title' => 'Later', 'sort_order' => 2]);
    $first = Menu::factory()->header()->create(['title' => 'First', 'sort_order' => 1]);
    $activeChild = Menu::factory()->header()->create([
        'title' => 'Active Child',
        'parent_id' => $first->id,
        'sort_order' => 2,
    ]);
    Menu::factory()->inactive()->header()->create([
        'title' => 'Inactive Child',
        'parent_id' => $first->id,
        'sort_order' => 1,
    ]);
    $inactiveParent = Menu::factory()->inactive()->header()->create(['title' => 'Inactive Parent']);
    Menu::factory()->header()->create(['title' => 'Hidden With Parent', 'parent_id' => $inactiveParent->id]);

    getJson('/api/v1/public/configuration')
        ->assertOk()
        ->assertJsonPath('data.menus.home_header.0.id', $first->id)
        ->assertJsonPath('data.menus.home_header.0.children.0.id', $activeChild->id)
        ->assertJsonPath('data.menus.home_header.1.id', $later->id)
        ->assertJsonMissing(['title' => 'Inactive Child'])
        ->assertJsonMissing(['title' => 'Inactive Parent'])
        ->assertJsonMissing(['title' => 'Hidden With Parent']);
});
