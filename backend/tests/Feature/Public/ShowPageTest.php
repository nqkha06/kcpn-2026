<?php

use App\Models\Page;

use function Pest\Laravel\getJson;

test('the public page API returns a published page', function () {
    $page = Page::query()->create([
        'title' => 'Điều khoản',
        'slug' => 'dieu-khoan',
        'content' => '<p>Nội dung</p>',
        'status' => 'published',
    ]);

    getJson('/api/v1/public/pages/dieu-khoan')
        ->assertOk()
        ->assertHeader('Cache-Control', 'max-age=60, public')
        ->assertJsonPath('data.id', $page->id);
});

test('the public page API rejects a draft page', function () {
    Page::query()->create(['title' => 'Bản nháp', 'slug' => 'ban-nhap', 'status' => 'draft']);

    getJson('/api/v1/public/pages/ban-nhap')->assertNotFound();
});

test('the public page API returns not found for an unknown slug', function () {
    getJson('/api/v1/public/pages/missing-page')->assertNotFound();
});

test('the public page API supports nested slugs', function () {
    $page = Page::query()->create([
        'title' => 'Privacy Policy',
        'slug' => 'legal/privacy',
        'content' => '<p>Privacy</p>',
        'status' => 'published',
    ]);

    getJson('/api/v1/public/pages/legal/privacy')
        ->assertOk()
        ->assertJsonPath('data.id', $page->id)
        ->assertJsonPath('data.slug', 'legal/privacy');
});

test('public page responses do not expose author or internal category data', function () {
    $page = Page::query()->create([
        'user_id' => adminUser()->id,
        'title' => 'Public Page',
        'slug' => 'public-page',
        'status' => 'published',
    ]);

    getJson('/api/v1/public/pages/public-page')
        ->assertOk()
        ->assertJsonMissingPath('data.user_id')
        ->assertJsonMissingPath('data.author')
        ->assertJsonMissingPath('data.category_id');
});
