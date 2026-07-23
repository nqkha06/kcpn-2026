<?php

namespace App\Services\Admin;

use App\Models\Setting;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

final class AdminAppearanceService
{
    private const KEY_LOGO_LIGHT = 'appearance.logo_light';

    private const KEY_LOGO_DARK = 'appearance.logo_dark';

    private const KEY_FAVICON = 'appearance.favicon';

    private const KEY_SOCIAL_IMAGE = 'appearance.social_image';

    private const KEY_GENERAL = 'appearance.general';

    private const FILE_FIELDS = [
        'logo_light' => self::KEY_LOGO_LIGHT,
        'logo_dark' => self::KEY_LOGO_DARK,
        'favicon' => self::KEY_FAVICON,
        'social_image' => self::KEY_SOCIAL_IMAGE,
    ];

    /** @return array<string, mixed> */
    public function data(): array
    {
        $paths = [];

        foreach (self::FILE_FIELDS as $field => $key) {
            $paths[$field] = $this->getSetting($key);
        }

        return [
            'languages' => $this->languages(),
            'logos' => $this->withUrls($paths),
            'general' => $this->getJsonSetting(self::KEY_GENERAL),
        ];
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @param  array<string, UploadedFile|null>  $files
     * @return array<string, mixed>
     */
    public function update(array $attributes, array $files): array
    {
        foreach (self::FILE_FIELDS as $field => $key) {
            $path = $this->storeUpload($files[$field] ?? null, $this->getSetting($key));
            $this->saveSetting($key, $path);
        }

        $generalInput = Arr::get($attributes, 'general', []);
        $generalPayload = [];

        foreach (array_column($this->languages(), 'code') as $code) {
            $entry = is_array($generalInput[$code] ?? null) ? $generalInput[$code] : [];
            $generalPayload[$code] = [
                'site_name' => (string) Arr::get($entry, 'site_name', ''),
                'site_title' => (string) Arr::get($entry, 'site_title', ''),
                'tagline' => (string) Arr::get($entry, 'tagline', ''),
                'meta_description' => (string) Arr::get($entry, 'meta_description', ''),
            ];
        }

        $this->saveSetting(self::KEY_GENERAL, json_encode($generalPayload, JSON_THROW_ON_ERROR));

        return $this->data();
    }

    /**
     * @return array<int, array{id: int, name: string, code: string, locale: string, is_default: bool}>
     */
    private function languages(): array
    {
        $defaultLocale = strtolower((string) config('app.locale', 'vi'));

        return collect(config('app.supported_locales', ['vi', 'en']))
            ->filter(fn (mixed $code): bool => is_string($code) && trim($code) !== '')
            ->values()
            ->map(function (string $code, int $index) use ($defaultLocale): array {
                $normalizedCode = strtolower($code);

                return [
                    'id' => $index + 1,
                    'name' => strtoupper($normalizedCode),
                    'code' => $normalizedCode,
                    'locale' => $normalizedCode,
                    'is_default' => $normalizedCode === $defaultLocale,
                ];
            })
            ->all();
    }

    private function storeUpload(?UploadedFile $file, ?string $current): ?string
    {
        if ($file === null) {
            return $current;
        }

        $directory = public_path('settings');
        File::ensureDirectoryExists($directory, 0755);
        $name = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME));
        $extension = strtolower($file->getClientOriginalExtension());
        $filename = ($name !== '' ? $name.'-' : '').Str::random(8).'.'.$extension;
        $file->move($directory, $filename);

        return 'settings/'.$filename;
    }

    private function getSetting(string $key, ?string $default = null): ?string
    {
        return Setting::query()->where('key', $key)->value('value') ?? $default;
    }

    /** @return array<string, mixed> */
    private function getJsonSetting(string $key): array
    {
        $raw = $this->getSetting($key);

        if ($raw === null || $raw === '') {
            return [];
        }

        $decoded = json_decode($raw, true);

        return is_array($decoded) ? $decoded : [];
    }

    private function saveSetting(string $key, ?string $value): void
    {
        Setting::query()->updateOrCreate(['key' => $key], ['value' => $value ?? '']);
    }

    /**
     * @param  array<string, string|null>  $paths
     * @return array<string, array{path: string|null, url: string|null}>
     */
    private function withUrls(array $paths): array
    {
        return collect($paths)
            ->map(fn (?string $path): array => [
                'path' => $path,
                'url' => $this->toUrl($path),
            ])
            ->all();
    }

    private function toUrl(?string $path): ?string
    {
        if ($path === null || $path === '') {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        return asset($path);
    }
}
