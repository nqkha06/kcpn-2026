<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\Controller;
use App\Models\Language;
use App\Support\Crud\IndexQuery;
use App\Support\Crud\PersistAction;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class LanguageController extends Controller
{
    public function index(Request $request)
    {
        $languages = IndexQuery::for(Language::class)
            ->filters([
                'search' => ['type' => 'like_any', 'cols' => ['name', 'locale', 'code']],
                'is_default' => ['type' => 'eq', 'col' => 'is_default'],
                'is_rtl' => ['type' => 'eq', 'col' => 'is_rtl'],
            ])
            ->sorts(['order', 'name', 'code', 'locale', 'is_default'], 'order', 'asc')
            ->paginate($request);

        if ($request->wantsJson()) {
            return response()->json($languages);
        }

        return Inertia::render('admin/settings/languages/list', [
            'languages' => $languages->items(),
            'pagination' => [
                'current_page' => $languages->currentPage(),
                'last_page' => $languages->lastPage(),
                'per_page' => $languages->perPage(),
                'total' => $languages->total(),
                'from' => $languages->firstItem(),
                'to' => $languages->lastItem(),
            ],
            'filters' => [
                'search' => $request->input('search'),
                'sort' => $request->input('sort'),
                'dir' => $request->input('dir'),
                'sort_by' => $request->input('sort'),
                'sort_direction' => $request->input('dir'),
                'is_default' => $request->input('is_default'),
                'is_rtl' => $request->input('is_rtl'),
            ],
        ]);
    }

    public function create(Request $request): InertiaResponse|\Illuminate\Http\RedirectResponse
    {
        return Inertia::render('admin/settings/languages/add');
    }

    public function store(Request $request, PersistAction $persist)
    {
        $data = $this->validated($request, true);

        $language = $persist->create(Language::class, $data);

        if ($data['is_default'] ?? false) {
            Language::where('id', '!=', $language->id)->update(['is_default' => false]);
        }

        if ($request->wantsJson()) {
            return response()->json($language, Response::HTTP_CREATED);
        }

        return redirect()->route('admin.languages.index')->with('success', 'Language created');
    }

    public function edit(Request $request, Language $language): InertiaResponse|\Illuminate\Http\RedirectResponse
    {
        return Inertia::render('admin/settings/languages/edit', [
            'language' => $language,
        ]);
    }

    public function update(Request $request, Language $language, PersistAction $persist)
    {
        $data = $this->validated($request, false, $language->id);

        $language = $persist->update($language, $data, false);

        if ($data['is_default'] ?? false) {
            Language::where('id', '!=', $language->id)->update(['is_default' => false]);
        }

        if ($request->wantsJson()) {
            return response()->json($language);
        }

        return redirect()->route('admin.languages.index')->with('success', 'Language updated');
    }

    public function destroy(Request $request, Language $language)
    {
        $language->delete();

        if ($request->wantsJson()) {
            return response()->json(['deleted' => true]);
        }

        return redirect()->route('admin.languages.index')->with('success', 'Language deleted');
    }

    private function validated(Request $request, bool $isCreate = true, ?int $ignoreId = null): array
    {
        $uniqueCode = 'unique:languages,code'.($ignoreId ? ','.$ignoreId : '');

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'locale' => ['required', 'string', 'max:50'],
            'code' => ['required', 'string', 'max:50', $uniqueCode],
            'flag' => ['nullable', 'string', 'max:255'],
            'is_default' => ['nullable', 'boolean'],
            'is_rtl' => ['nullable', 'boolean'],
            'order' => ['nullable', 'integer', 'min:0'],
        ]);

        $data['is_default'] = (bool) ($data['is_default'] ?? false);
        $data['is_rtl'] = (bool) ($data['is_rtl'] ?? false);
        $data['order'] = $data['order'] ?? 0;

        return $data;
    }
}
