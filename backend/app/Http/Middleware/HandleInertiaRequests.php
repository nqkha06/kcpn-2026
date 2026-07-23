<?php

namespace App\Http\Middleware;

use App\Models\Menu;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'locale' => fn () => app()->getLocale(),
            'defaultLocale' => config('app.locale', 'vi'),
            'locales' => config('app.supported_locales', ['vi', 'en']),
            'routes' => fn () => collect(Route::getRoutes()->getRoutesByName())
                ->map(fn ($route) => $route->uri())
                ->all(),
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
                'info' => fn () => $request->session()->get('info'),
                'warning' => fn () => $request->session()->get('warning'),
            ],

            'name' => config('app.name'),
            'auth' => [
                'user' => $request->user(),
            ],
            'userPreferenceCurrency' => fn (): ?string => $request->user()?->getMeta('currency'),
            'publicMenus' => fn (): array => [
                'homeHeader' => $this->publicMenus('home.header'),
                'homeFooter' => $this->publicMenus('home.footer'),
                'userHeader' => $this->publicMenus('user.header'),
            ],
            'navigation' => fn (): ?array => $this->expenseNavigation($request),
            'profile' => fn (): ?array => $this->expenseProfile($request),
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
            'appearanceOptions' => fn (): array => $this->appearanceOptions($request),
        ];
    }

    private function appearanceOptions(Request $request): array
    {
        if (! Schema::hasTable('settings')) {
            return [
                'logo_light' => null,
                'logo_dark' => null,
                'favicon' => null,
                'social_image' => null,
                'site_name' => config('app.name'),
                'site_title' => config('app.name'),
                'tagline' => null,
                'meta_description' => null,
            ];
        }

        $logoLight = Setting::query()->where('key', 'appearance.logo_light')->value('value');
        $logoDark = Setting::query()->where('key', 'appearance.logo_dark')->value('value');
        $favicon = Setting::query()->where('key', 'appearance.favicon')->value('value');
        $socialImage = Setting::query()->where('key', 'appearance.social_image')->value('value');

        $general = Setting::query()->where('key', 'appearance.general')->value('value');
        $decodedGeneral = json_decode((string) $general, true);
        $locale = app()->getLocale();

        $localized = is_array($decodedGeneral) && isset($decodedGeneral[$locale]) && is_array($decodedGeneral[$locale])
            ? $decodedGeneral[$locale]
            : [];

        return [
            'logo_light' => $this->toAssetUrl($logoLight),
            'logo_dark' => $this->toAssetUrl($logoDark),
            'favicon' => $this->toAssetUrl($favicon),
            'social_image' => $this->toAssetUrl($socialImage),
            'site_name' => $localized['site_name'] ?? config('app.name'),
            'site_title' => $localized['site_title'] ?? config('app.name'),
            'tagline' => $localized['tagline'] ?? null,
            'meta_description' => $localized['meta_description'] ?? null,
        ];
    }

    private function toAssetUrl(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        return asset($path);
    }

    private function publicMenus(string $canonical): array
    {
        return Menu::query()
            ->where('canonical', $canonical)
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
            ->map(fn (Menu $menu): array => [
                'id' => $menu->id,
                'title' => $menu->title,
                'url' => $menu->url,
                'target' => $menu->target,
                'canonical' => $menu->canonical,
                'children' => $menu->children->map(fn (Menu $child): array => [
                    'id' => $child->id,
                    'title' => $child->title,
                    'url' => $child->url,
                    'target' => $child->target,
                    'canonical' => $child->canonical,
                ])->all(),
            ])
            ->all();
    }

    private function expenseNavigation(Request $request): ?array
    {
        if (! $request->routeIs('expense.*') && ! $request->routeIs('user.settings*')) {
            return null;
        }

        return [
            ['label' => 'Tổng quan', 'href' => route('expense.dashboard')],
            ['label' => 'Giao dịch', 'href' => route('expense.transactions')],
            ['label' => 'Ngân sách', 'href' => route('expense.budgets')],
            ['label' => 'Ví tiền', 'href' => route('expense.wallets')],
            ['label' => 'Cài đặt', 'href' => route('user.settings')],
        ];
    }

    private function expenseProfile(Request $request): ?array
    {
        if (! $request->routeIs('expense.*') && ! $request->routeIs('user.settings*')) {
            return null;
        }

        $user = $request->user();
        $name = $user?->name ?? 'Khách';

        return [
            'name' => $name,
            'email' => $user?->email ?? 'khach@example.com',
            'initials' => collect(explode(' ', (string) $name))
                ->filter()
                ->take(2)
                ->map(fn (string $part): string => strtoupper(substr($part, 0, 1)))
                ->implode(''),
        ];
    }
}
