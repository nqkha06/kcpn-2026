<?php

use App\Models\Page;

test('the public page API returns a published page', function () {
    $page = Page::query()->create([
        'title' => 'Điều khoản',
        'slug' => 'dieu-khoan',
        'content' => '<p>Nội dung</p>',
        'status' => 'published',
    ]);

    $this->getJson('/api/v1/public/pages/dieu-khoan')
        ->assertOk()
        ->assertJsonPath('data.id', $page->id);
});

test('the public page API rejects a draft page', function () {
    Page::query()->create(['title' => 'Bản nháp', 'slug' => 'ban-nhap', 'status' => 'draft']);

    $this->getJson('/api/v1/public/pages/ban-nhap')->assertNotFound();
});
