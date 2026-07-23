<?php

namespace App\Services;

use App\Enums\BaseStatusEnum;
use App\Models\Menu;
use App\Models\Page;
use App\Models\Setting;
use Illuminate\Support\Facades\Schema;
use Laravel\Fortify\Features;

final class PublicSiteService
{
    /**
     * @return array<string, mixed>
     */
    public function configuration(string $locale): array
    {
        return [
            'locale' => $locale,
            'default_locale' => config('app.locale', 'en'),
            'locales' => config('app.supported_locales', ['vi', 'en']),
            'can_register' => Features::enabled(Features::registration()),
            'appearance' => $this->appearanceOptions($locale),
            'menus' => $this->publicMenus(),
        ];
    }

    public function publishedPage(string $slug): Page
    {
        return Page::query()
            ->where('slug', $slug)
            ->where('status', BaseStatusEnum::PUBLISHED->value)
            ->firstOrFail();
    }

    /**
     * @return array<string, mixed>
     */
    private function appearanceOptions(string $locale): array
    {
        $defaults = [
            'logo_light' => null,
            'logo_dark' => null,
            'favicon' => null,
            'social_image' => null,
            'site_name' => config('app.name'),
            'site_title' => config('app.name'),
            'tagline' => null,
            'meta_description' => null,
        ];

        if (! Schema::hasTable('settings')) {
            return $defaults;
        }

        $settings = Setting::query()
            ->whereIn('key', [
                'appearance.logo_light',
                'appearance.logo_dark',
                'appearance.favicon',
                'appearance.social_image',
                'appearance.general',
            ])
            ->pluck('value', 'key');

        $general = json_decode((string) $settings->get('appearance.general'), true);
        $localized = is_array($general)
            && isset($general[$locale])
            && is_array($general[$locale])
                ? $general[$locale]
                : [];

        return [
            'logo_light' => $this->toAssetUrl($settings->get('appearance.logo_light')),
            'logo_dark' => $this->toAssetUrl($settings->get('appearance.logo_dark')),
            'favicon' => $this->toAssetUrl($settings->get('appearance.favicon')),
            'social_image' => $this->toAssetUrl($settings->get('appearance.social_image')),
            'site_name' => $localized['site_name'] ?? $defaults['site_name'],
            'site_title' => $localized['site_title'] ?? $defaults['site_title'],
            'tagline' => $localized['tagline'] ?? null,
            'meta_description' => $localized['meta_description'] ?? null,
        ];
    }

    /**
     * @return array<string, array<int, array<string, mixed>>>
     */
    private function publicMenus(): array
    {
        $canonicals = ['home.header', 'home.footer', 'user.header'];
        $emptyMenus = array_fill_keys($canonicals, []);

        if (! Schema::hasTable('menus')) {
            return $emptyMenus;
        }

        $menus = Menu::query()
            ->whereIn('canonical', $canonicals)
            ->where('status', 'active')
            ->whereNull('parent_id')
            ->with([
                'children' => fn ($query) => $query
                    ->where('status', 'active')
                    ->orderBy('sort_order')
                    ->orderBy('id'),
            ])
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->groupBy('canonical');

        foreach ($canonicals as $canonical) {
            $emptyMenus[$canonical] = $menus->get($canonical, collect())
                ->map(fn (Menu $menu): array => $this->menuToArray($menu))
                ->values()
                ->all();
        }

        return $emptyMenus;
    }

    /**
     * @return array<string, mixed>
     */
    private function menuToArray(Menu $menu): array
    {
        return [
            'id' => $menu->id,
            'title' => $menu->title,
            'url' => $menu->url,
            'target' => $menu->target,
            'canonical' => $menu->canonical,
            'children' => $menu->children
                ->map(fn (Menu $child): array => [
                    'id' => $child->id,
                    'title' => $child->title,
                    'url' => $child->url,
                    'target' => $child->target,
                    'canonical' => $child->canonical,
                ])
                ->values()
                ->all(),
        ];
    }

    private function toAssetUrl(?string $path): ?string
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
