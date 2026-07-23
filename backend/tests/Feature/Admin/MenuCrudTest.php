<?php

use App\Models\Menu;

test('admin can view menu index', function () {
    $admin = adminUser();

    $this->actingAs($admin);
    $this->withoutVite();

    $this->get(route('admin.menus.index'))
        ->assertOk();
});

test('admin can create a menu item', function () {
    $admin = adminUser();

    $this->actingAs($admin);

    $payload = [
        'title' => 'Pricing',
        'url' => '/pricing',
        'canonical' => 'home.header',
        'parent_id' => null,
        'sort_order' => 2,
        'target' => '_self',
        'status' => 'active',
    ];

    $this->post(route('admin.menus.store'), $payload)
        ->assertRedirect(route('admin.menus.index'));

    $menuExists = Menu::query()
        ->where('title', 'Pricing')
        ->where('canonical', 'home.header')
        ->where('status', 'active')
        ->exists();

    expect($menuExists)->toBeTrue();
});

test('admin can update a menu item', function () {
    $admin = adminUser();
    $menu = Menu::factory()->create();

    $this->actingAs($admin);

    $payload = [
        'title' => 'About Us',
        'url' => '/about-us',
        'canonical' => 'home.footer',
        'parent_id' => null,
        'sort_order' => 5,
        'target' => '_blank',
        'status' => 'inactive',
    ];

    $this->put(route('admin.menus.update', $menu), $payload)
        ->assertRedirect(route('admin.menus.index'));

    $updatedExists = Menu::query()
        ->whereKey($menu->id)
        ->where('title', 'About Us')
        ->where('canonical', 'home.footer')
        ->where('target', '_blank')
        ->where('status', 'inactive')
        ->exists();

    expect($updatedExists)->toBeTrue();
});

test('admin can delete a menu item', function () {
    $admin = adminUser();
    $menu = Menu::factory()->create();

    $this->actingAs($admin);

    $this->delete(route('admin.menus.destroy', $menu))
        ->assertRedirect(route('admin.menus.index'));

    $menuStillExists = Menu::query()
        ->whereKey($menu->id)
        ->exists();

    expect($menuStillExists)->toBeFalse();
});
