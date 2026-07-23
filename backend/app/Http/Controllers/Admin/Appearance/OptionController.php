<?php

namespace App\Http\Controllers\Admin\Appearance;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class OptionController extends Controller
{
    private const KEY_LOGO_LIGHT = 'appearance.logo_light';

    private const KEY_LOGO_DARK = 'appearance.logo_dark';

    private const KEY_FAVICON = 'appearance.favicon';

    private const KEY_SOCIAL_IMAGE = 'appearance.social_image';

    private const KEY_GENERAL = 'appearance.general';

    public function index(Request $request): InertiaResponse
    {
        $languages = $this->languages();

        $logos = [
            'logo_light' => $this->getSetting(self::KEY_LOGO_LIGHT),
            'logo_dark' => $this->getSetting(self::KEY_LOGO_DARK),
            'favicon' => $this->getSetting(self::KEY_FAVICON),
            'social_image' => $this->getSetting(self::KEY_SOCIAL_IMAGE),
        ];

        $general = $this->getJsonSetting(self::KEY_GENERAL, []);

        return Inertia::render('Admin/settings/appearance/options', [
            'languages' => $languages,
            'logos' => $this->withUrls($logos),
            'general' => $general,
        ]);
    }

    public function store(Request $request)
    {
        $languageCodes = collect($this->languages())->pluck('code')->all();

        $imageMimes = 'jpg,jpeg,png,gif,webp,svg,ico';
        $imageMimeTypes = 'image/jpeg,image/png,image/gif,image/webp,image/svg+xml,image/x-icon,image/vnd.microsoft.icon';

        $rules = [
            'logo_light' => ['nullable', 'mimes:'.$imageMimes, 'mimetypes:'.$imageMimeTypes, 'max:4096'],
            'logo_dark' => ['nullable', 'mimes:'.$imageMimes, 'mimetypes:'.$imageMimeTypes, 'max:4096'],
            'favicon' => ['nullable', 'mimes:'.$imageMimes, 'mimetypes:'.$imageMimeTypes, 'max:4096'],
            'social_image' => ['nullable', 'mimes:'.$imageMimes, 'mimetypes:'.$imageMimeTypes, 'max:4096'],
            'general' => ['nullable', 'array'],
        ];

        foreach ($languageCodes as $code) {
            $rules["general.$code.site_name"] = ['nullable', 'string', 'max:255'];
            $rules["general.$code.site_title"] = ['nullable', 'string', 'max:255'];
            $rules["general.$code.tagline"] = ['nullable', 'string', 'max:255'];
            $rules["general.$code.meta_description"] = ['nullable', 'string', 'max:500'];
        }

        $data = $request->validate($rules);

        // Handle uploads
        $logoLightPath = $this->handleUpload($request, 'logo_light', $this->getSetting(self::KEY_LOGO_LIGHT));
        $logoDarkPath = $this->handleUpload($request, 'logo_dark', $this->getSetting(self::KEY_LOGO_DARK));
        $faviconPath = $this->handleUpload($request, 'favicon', $this->getSetting(self::KEY_FAVICON));
        $socialImagePath = $this->handleUpload($request, 'social_image', $this->getSetting(self::KEY_SOCIAL_IMAGE));

        $this->saveSetting(self::KEY_LOGO_LIGHT, $logoLightPath);
        $this->saveSetting(self::KEY_LOGO_DARK, $logoDarkPath);
        $this->saveSetting(self::KEY_FAVICON, $faviconPath);
        $this->saveSetting(self::KEY_SOCIAL_IMAGE, $socialImagePath);

        $generalInput = Arr::get($data, 'general', []);
        $generalPayload = [];
        foreach ($languageCodes as $code) {
            $entry = $generalInput[$code] ?? [];
            $generalPayload[$code] = [
                'site_name' => Arr::get($entry, 'site_name', ''),
                'site_title' => Arr::get($entry, 'site_title', ''),
                'tagline' => Arr::get($entry, 'tagline', ''),
                'meta_description' => Arr::get($entry, 'meta_description', ''),
            ];
        }

        $this->saveSetting(self::KEY_GENERAL, json_encode($generalPayload));

        if ($request->wantsJson()) {
            return response()->json([
                'logos' => $this->withUrls([
                    'logo_light' => $logoLightPath,
                    'logo_dark' => $logoDarkPath,
                    'favicon' => $faviconPath,
                    'social_image' => $socialImagePath,
                ]),
                'general' => $generalPayload,
            ]);
        }

        return Redirect::back()->with('success', 'Appearance settings updated');
    }

    /**
     * @return array<int, array{id: int, name: string, code: string, locale: string, is_default: bool}>
     */
    private function languages(): array
    {
        $supportedLocales = config('app.supported_locales', ['vi', 'en']);
        $defaultLocale = (string) config('app.locale', 'vi');

        return collect($supportedLocales)
            ->filter(fn ($code): bool => is_string($code) && trim($code) !== '')
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

    private function handleUpload(Request $request, string $field, ?string $current): ?string
    {
        if (! $request->hasFile($field)) {
            return $current;
        }

        $file = $request->file($field);
        $dir = public_path('settings');
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $name = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME));
        $ext = $file->getClientOriginalExtension();
        $filename = $name ? $name.'-'.Str::random(8).'.'.$ext : Str::random(12).'.'.$ext;

        $file->move($dir, $filename);

        return 'settings/'.$filename;
    }

    private function getSetting(string $key, $default = null)
    {
        return Setting::query()->where('key', $key)->value('value') ?? $default;
    }

    private function getJsonSetting(string $key, $default = []): array
    {
        $raw = $this->getSetting($key);

        if (! $raw) {
            return $default;
        }

        $decoded = json_decode($raw, true);

        return is_array($decoded) ? $decoded : $default;
    }

    private function saveSetting(string $key, $value): void
    {

        Setting::updateOrCreate(['key' => $key], ['value' => $value ?? '']);
    }

    private function withUrls(array $paths): array
    {
        return collect($paths)
            ->map(fn ($path) => [
                'path' => $path,
                'url' => $this->toUrl($path),
            ])
            ->toArray();
    }

    private function toUrl(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        return asset($path);
    }
}
