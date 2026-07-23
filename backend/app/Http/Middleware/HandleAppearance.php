<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class HandleAppearance
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        View::share('appearance', $request->cookie('appearance') ?? 'system');
        View::share('appearanceMeta', $this->appearanceMeta());

        return $next($request);
    }

    private function appearanceMeta(): array
    {
        if (! Schema::hasTable('settings')) {
            return [
                'favicon' => null,
                'site_title' => config('app.name', 'Laravel'),
                'meta_description' => null,
            ];
        }

        $favicon = Setting::query()->where('key', 'appearance.favicon')->value('value');
        $general = Setting::query()->where('key', 'appearance.general')->value('value');
        $decodedGeneral = json_decode((string) $general, true);
        $locale = app()->getLocale();

        $localized = is_array($decodedGeneral) && isset($decodedGeneral[$locale]) && is_array($decodedGeneral[$locale])
            ? $decodedGeneral[$locale]
            : [];

        return [
            'favicon' => $this->toUrl($favicon),
            'site_title' => $localized['site_title'] ?? config('app.name', 'Laravel'),
            'meta_description' => $localized['meta_description'] ?? null,
        ];
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
