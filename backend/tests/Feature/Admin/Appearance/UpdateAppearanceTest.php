<?php

use App\Models\Setting;

test('an admin can update appearance settings', function () {
    $this->actingAs(adminUser())
        ->postJson('/api/v1/admin/appearance', [
            'general' => [
                'vi' => ['site_name' => 'Cashback API', 'site_title' => 'Quản lý chi tiêu'],
                'en' => ['site_name' => 'Cashback API', 'site_title' => 'Expense manager'],
            ],
        ])
        ->assertOk()
        ->assertJsonPath('data.general.vi.site_name', 'Cashback API');

    $general = json_decode(Setting::query()->where('key', 'appearance.general')->value('value'), true);

    expect($general['vi']['site_name'])->toBe('Cashback API');
});
