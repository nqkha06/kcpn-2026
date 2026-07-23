<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\MenuRequest;
use App\Models\Menu;
use App\Services\MenuService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MenuController extends Controller
{
    protected $service;

    public function __construct(MenuService $service)
    {
        $this->service = $service;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $sortBy = (string) $request->get('sort_by', 'sort_order');
        $sortDirection = (string) $request->get('sort_direction', 'asc');
        $perPage = (int) $request->get('per_page', 10);

        $menus = $this->service->paginate([
            'search' => $request->get('search'),
            'status' => $request->get('status'),
            'canonical' => $request->get('canonical'),
            'sort_by' => $sortBy,
            'sort_direction' => $sortDirection,
            'per_page' => $perPage,
            'include' => 'parent',
        ]);

        $items = collect($menus->items())->map(function (Menu $menu): array {
            return [
                'id' => $menu->id,
                'title' => $menu->title,
                'url' => $menu->url,
                'canonical' => $menu->canonical,
                'sort_order' => $menu->sort_order,
                'target' => $menu->target,
                'status' => $menu->status,
                'parent_id' => $menu->parent_id,
                'parent_title' => $menu->parent?->title,
                'created_at' => optional($menu->created_at)->toDateTimeString(),
            ];
        })->all();

        return Inertia::render('Admin/menus/list', [
            'menus' => $items,
            'pagination' => [
                'current_page' => $menus->currentPage(),
                'last_page' => $menus->lastPage(),
                'per_page' => $menus->perPage(),
                'total' => $menus->total(),
                'from' => $menus->firstItem(),
                'to' => $menus->lastItem(),
            ],
            'filters' => [
                'search' => $request->input('search'),
                'status' => $request->input('status'),
                'canonical' => $request->input('canonical'),
                'sort_by' => $sortBy,
                'sort_direction' => $sortDirection,
                'per_page' => $perPage,
            ],
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        return Inertia::render('Admin/menus/add', [
            'parentMenus' => $this->parentMenuOptions(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(MenuRequest $request): RedirectResponse
    {
        $this->service->create($this->normalizePayload($request->validated()));

        return redirect()
            ->route('admin.menus.index')
            ->with('success', 'Menu created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Menu $menu): RedirectResponse
    {
        return redirect()->route('admin.menus.edit', $menu);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Menu $menu): Response
    {
        return Inertia::render('Admin/menus/edit', [
            'menu' => [
                'id' => $menu->id,
                'title' => $menu->title,
                'url' => $menu->url,
                'canonical' => $menu->canonical,
                'sort_order' => $menu->sort_order,
                'target' => $menu->target,
                'status' => $menu->status,
                'parent_id' => $menu->parent_id,
            ],
            'parentMenus' => $this->parentMenuOptions($menu),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(MenuRequest $request, Menu $menu): RedirectResponse
    {
        $this->service->update($menu->id, $this->normalizePayload($request->validated()));

        return redirect()
            ->route('admin.menus.index')
            ->with('success', 'Menu updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Menu $menu): RedirectResponse
    {
        $menu->delete();

        return redirect()
            ->route('admin.menus.index')
            ->with('success', 'Menu deleted successfully.');
    }

    private function parentMenuOptions(?Menu $menu = null): array
    {
        return Menu::query()
            ->whereNull('parent_id')
            ->when($menu !== null, fn ($query) => $query->whereKeyNot($menu->getKey()))
            ->orderBy('title')
            ->get(['id', 'title', 'canonical'])
            ->map(fn (Menu $item): array => [
                'id' => $item->id,
                'title' => $item->title,
                'canonical' => $item->canonical,
            ])
            ->all();
    }

    private function normalizePayload(array $payload): array
    {
        $payload['sort_order'] = (int) ($payload['sort_order'] ?? 0);
        $payload['parent_id'] = $payload['parent_id'] ?? null;

        if (isset($payload['url']) && is_string($payload['url'])) {
            $payload['url'] = trim($payload['url']) !== '' ? trim($payload['url']) : null;
        }

        if (! empty($payload['parent_id'])) {
            $parent = Menu::query()->find($payload['parent_id']);
            if ($parent && isset($payload['canonical'])) {
                $payload['canonical'] = $parent->canonical;
            }
        }

        return $payload;
    }
}
