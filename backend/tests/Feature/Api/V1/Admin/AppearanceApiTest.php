<?php

use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Spatie\Permission\Models\Role;

function appearanceApiActor(string $role): User
{
    $user = User::factory()->create();
    $user->assignRole(Role::findOrCreate($role, 'web'));

    return $user;
}

beforeEach(function (): void {
    $this->uploadedAppearancePaths = [];
    $this->withHeaders([
        'Accept' => 'application/json',
        'Origin' => 'http://localhost:3000',
        'Referer' => 'http://localhost:3000/admin/settings/appearance',
    ]);
});

afterEach(function (): void {
    foreach ($this->uploadedAppearancePaths as $path) {
        File::delete(public_path($path));
    }
});

test('appearance routes reject guests and non admin users', function () {
    $this->getJson('/api/v1/admin/appearance')
        ->assertUnauthorized()
        ->assertJsonPath('message', 'Unauthenticated');

    $this->actingAs(appearanceApiActor('user'), 'web')
        ->getJson('/api/v1/admin/appearance')
        ->assertForbidden()
        ->assertJsonPath('message', 'Forbidden');
});

test('admin can read and update localized appearance settings', function () {
    $admin = appearanceApiActor('admin');
    $this->actingAs($admin, 'web');

    $this->getJson('/api/v1/admin/appearance')
        ->assertOk()
        ->assertJsonPath('data.languages.0.code', 'vi')
        ->assertJsonPath('data.languages.1.code', 'en')
        ->assertJsonPath('data.logos.logo_light.path', null);

    $this->postJson('/api/v1/admin/appearance', [
        'general' => [
            'vi' => [
                'site_name' => 'Hoàn tiền',
                'site_title' => 'Quản lý chi tiêu',
                'tagline' => 'Chi tiêu thông minh',
                'meta_description' => 'Mô tả tiếng Việt',
            ],
            'en' => [
                'site_name' => 'Cashback',
                'site_title' => 'Expense Manager',
                'tagline' => 'Spend smarter',
                'meta_description' => 'English description',
            ],
        ],
    ])
        ->assertOk()
        ->assertJsonPath('message', 'Appearance settings updated successfully')
        ->assertJsonPath('data.general.vi.site_name', 'Hoàn tiền')
        ->assertJsonPath('data.general.en.site_name', 'Cashback');

    $this->assertDatabaseHas('settings', [
        'key' => 'appearance.general',
    ]);
    $general = json_decode((string) Setting::query()->where('key', 'appearance.general')->value('value'), true);
    expect($general['vi']['site_title'])->toBe('Quản lý chi tiêu')
        ->and($general['en']['tagline'])->toBe('Spend smarter');
});

test('admin can upload appearance image and receives its public url', function () {
    $admin = appearanceApiActor('admin');
    $this->actingAs($admin, 'web');

    $response = $this->post('/api/v1/admin/appearance', [
        'logo_light' => UploadedFile::fake()->image('brand-logo.png', 64, 64),
        'general' => [
            'vi' => ['site_name' => 'Logo test'],
            'en' => ['site_name' => 'Logo test'],
        ],
    ]);

    $response
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.logos.logo_light.path', fn ($path): bool => is_string($path) && str_starts_with($path, 'settings/brand-logo-'))
        ->assertJsonPath('data.logos.logo_light.url', fn ($url): bool => is_string($url) && str_contains($url, '/settings/brand-logo-'));

    $path = $response->json('data.logos.logo_light.path');
    $this->uploadedAppearancePaths[] = $path;

    expect(File::exists(public_path($path)))->toBeTrue();
    $this->assertDatabaseHas('settings', [
        'key' => 'appearance.logo_light',
        'value' => $path,
    ]);
});

test('appearance api rejects unsupported and oversized uploads', function () {
    $admin = appearanceApiActor('admin');
    $this->actingAs($admin, 'web');

    $this->post('/api/v1/admin/appearance', [
        'logo_dark' => UploadedFile::fake()->create('payload.txt', 10, 'text/plain'),
        'favicon' => UploadedFile::fake()->image('large.png', 10, 10)->size(5000),
    ])
        ->assertUnprocessable()
        ->assertJsonPath('message', 'Validation failed')
        ->assertJsonValidationErrors(['logo_dark', 'favicon']);
});
