<?php

use App\Models\Menu;

test('the public configuration API returns active menus', function () {
    $menu = Menu::factory()->header()->create(['title' => 'Trang chủ']);
    Menu::factory()->inactive()->header()->create(['title' => 'Ẩn']);

    $this->getJson('/api/v1/public/configuration?locale=vi')
        ->assertOk()
        ->assertJsonPath('data.locale', 'vi')
        ->assertJsonPath('data.menus.home_header.0.id', $menu->id)
        ->assertJsonMissing(['title' => 'Ẩn']);
});
