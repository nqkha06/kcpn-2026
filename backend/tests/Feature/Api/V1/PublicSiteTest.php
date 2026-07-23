<?php

use App\Enums\BaseStatusEnum;
use App\Models\Menu;
use App\Models\Page;
use App\Models\Setting;

test('public configuration returns localized appearance and ordered active menus', function () {
    Setting::query()->create([
        'key' => 'appearance.logo_light',
        'value' => 'settings/logo-light.svg',
    ]);
    Setting::query()->create([
        'key' => 'appearance.general',
        'value' => json_encode([
            'vi' => [
                'site_name' => 'Chi Tiêu Thông Minh',
                'site_title' => 'Quản lý chi tiêu',
                'tagline' => 'Kiểm soát tài chính cá nhân',
                'meta_description' => 'Ứng dụng quản lý chi tiêu.',
            ],
            'en' => [
                'site_name' => 'Smart Spending',
            ],
        ], JSON_THROW_ON_ERROR),
    ]);

    $secondMenu = Menu::factory()->header()->create([
        'title' => 'Second',
        'sort_order' => 20,
    ]);
    $firstMenu = Menu::factory()->header()->create([
        'title' => 'First',
        'sort_order' => 10,
    ]);
    Menu::factory()->header()->create([
        'parent_id' => $firstMenu->id,
        'title' => 'Child',
        'sort_order' => 1,
    ]);
    Menu::factory()->header()->inactive()->create([
        'title' => 'Hidden',
    ]);

    $response = $this->getJson('/api/v1/public/configuration?locale=vi');

    $response
        ->assertOk()
        ->assertHeader('Cache-Control', 'max-age=60, public')
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.locale', 'vi')
        ->assertJsonPath('data.appearance.site_name', 'Chi Tiêu Thông Minh')
        ->assertJsonPath(
            'data.appearance.logo_light',
            rtrim((string) config('app.url'), '/').'/settings/logo-light.svg',
        )
        ->assertJsonPath('data.menus.home_header.0.id', $firstMenu->id)
        ->assertJsonPath('data.menus.home_header.0.children.0.title', 'Child')
        ->assertJsonPath('data.menus.home_header.1.id', $secondMenu->id)
        ->assertJsonCount(2, 'data.menus.home_header')
        ->assertJsonCount(0, 'data.menus.home_footer');
});

test('public configuration rejects unsupported locales', function () {
    $this->getJson('/api/v1/public/configuration?locale=fr')
        ->assertUnprocessable()
        ->assertJsonPath('success', false)
        ->assertJsonPath('message', 'Validation failed')
        ->assertJsonValidationErrors('locale');
});

test('published page can be fetched by a nested slug', function () {
    $page = Page::query()->create([
        'title' => 'Getting Started',
        'slug' => 'guides/getting-started',
        'image' => 'uploads/pages/guide.jpg',
        'content' => '<p>Welcome</p>',
        'meta_title' => 'Getting Started Guide',
        'meta_description' => 'Learn how to get started.',
        'meta_keywords' => 'guide,start',
        'status' => BaseStatusEnum::PUBLISHED,
    ]);

    $this->getJson('/api/v1/public/pages/guides/getting-started')
        ->assertOk()
        ->assertHeader('Cache-Control', 'max-age=60, public')
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.id', $page->id)
        ->assertJsonPath('data.slug', 'guides/getting-started')
        ->assertJsonPath(
            'data.image',
            rtrim((string) config('app.url'), '/').'/uploads/pages/guide.jpg',
        )
        ->assertJsonPath('data.content', '<p>Welcome</p>');
});

test('draft and missing pages are not publicly accessible', function () {
    Page::query()->create([
        'title' => 'Draft Page',
        'slug' => 'draft-page',
        'status' => BaseStatusEnum::DRAFT,
    ]);

    $this->getJson('/api/v1/public/pages/draft-page')
        ->assertNotFound()
        ->assertJsonPath('success', false)
        ->assertJsonPath('message', 'Resource not found');

    $this->getJson('/api/v1/public/pages/missing-page')
        ->assertNotFound()
        ->assertJsonPath('success', false)
        ->assertJsonPath('message', 'Resource not found');
});
