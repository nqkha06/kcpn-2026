<?php

use App\Models\Setting;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;
use function Pest\Laravel\postJson;

test('an admin can update appearance settings', function () {
    actingAs(adminUser())
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

test('a guest cannot update appearance settings', function () {
    postJson('/api/v1/admin/appearance', [
        'general' => ['vi' => ['site_name' => 'Guest Site']],
    ])->assertUnauthorized();

    assertDatabaseMissing('settings', ['key' => 'appearance.general']);
});

test('a regular user cannot update appearance settings', function () {
    actingAs(regularUser())
        ->postJson('/api/v1/admin/appearance', [
            'general' => ['vi' => ['site_name' => 'Unauthorized Site']],
        ])
        ->assertForbidden();
});

test('appearance update validates translated text lengths', function () {
    actingAs(adminUser())
        ->postJson('/api/v1/admin/appearance', [
            'general' => [
                'vi' => [
                    'site_name' => str_repeat('a', 256),
                    'meta_description' => str_repeat('a', 501),
                ],
            ],
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors([
            'general.vi.site_name',
            'general.vi.meta_description',
        ]);
});

test('appearance update rejects unsupported uploads', function () {
    actingAs(adminUser())
        ->post('/api/v1/admin/appearance', [
            'logo_light' => UploadedFile::fake()->create('logo.txt', 10, 'text/plain'),
        ], ['Accept' => 'application/json'])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('logo_light');
});

test('an admin can upload a logo and its path is persisted', function () {
    $response = actingAs(adminUser())
        ->post('/api/v1/admin/appearance', [
            'logo_light' => UploadedFile::fake()->image('brand-logo.png', 120, 40),
        ], ['Accept' => 'application/json'])
        ->assertOk();

    $path = $response->json('data.logos.logo_light.path');

    try {
        expect($path)->toStartWith('settings/brand-logo-');
        expect(File::exists(public_path($path)))->toBeTrue();
        expect(Setting::query()->where('key', 'appearance.logo_light')->value('value'))->toBe($path);
    } finally {
        File::delete(public_path($path));
    }
});

test('updating general appearance preserves existing logo paths', function () {
    Setting::query()->create([
        'key' => 'appearance.logo_light',
        'value' => 'settings/existing-logo.png',
    ]);

    actingAs(adminUser())
        ->postJson('/api/v1/admin/appearance', [
            'general' => ['en' => ['site_name' => 'Cashback']],
        ])
        ->assertOk()
        ->assertJsonPath('data.logos.logo_light.path', 'settings/existing-logo.png');

    assertDatabaseHas('settings', [
        'key' => 'appearance.logo_light',
        'value' => 'settings/existing-logo.png',
    ]);
});
