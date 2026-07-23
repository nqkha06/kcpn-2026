<?php

namespace App\Http\Controllers\Admin;

use App\Enums\BaseStatusEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PageRequest;
use App\Models\Page;
use App\Services\PageService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class PageController extends Controller
{
    protected $service;

    public function __construct(PageService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request): InertiaResponse
    {
        $sortBy = $request->get('sort_by', 'created_at');
        $sortDirection = $request->get('sort_direction', 'desc');
        $perPage = (int) $request->get('per_page', 10);
        $status = $request->get('status');

        $pages = $this->service->paginate([
            'search' => $request->get('search'),
            'status' => $status,
            'sort_by' => $sortBy,
            'sort_direction' => $sortDirection,
            'per_page' => $perPage,
        ]);

        $items = collect($pages->items())->map(function (Page $page) {
            return [
                'id' => $page->id,
                'title' => $page->title,
                'slug' => $page->slug,
                'status' => $page->status instanceof BaseStatusEnum ? $page->status->value : (string) $page->status,
                'created_at' => optional($page->created_at)->toDateTimeString(),
            ];
        });

        return Inertia::render('Admin/pages/list', [
            'pages' => $items,
            'pagination' => [
                'current_page' => $pages->currentPage(),
                'last_page' => $pages->lastPage(),
                'per_page' => $pages->perPage(),
                'total' => $pages->total(),
                'from' => $pages->firstItem(),
                'to' => $pages->lastItem(),
            ],
            'filters' => [
                'search' => $request->search,
                'status' => $status,
                'sort_by' => $sortBy,
                'sort_direction' => $sortDirection,
                'per_page' => $perPage,
            ],
        ]);
    }

    public function create(): InertiaResponse
    {
        return Inertia::render('Admin/pages/add');
    }

    public function store(PageRequest $request)
    {
        $data = $request->validated();

        $page = $this->service->create([
            'user_id' => $request->user()?->id,
            'title' => $data['title'],
            'image' => $data['image'] ?? null,
            'content' => $data['content'] ?? null,
            'meta_title' => $data['meta_title'] ?? null,
            'meta_description' => $data['meta_description'] ?? null,
            'meta_keywords' => $data['meta_keywords'] ?? null,
            'slug' => $this->resolveBaseSlug($data),
            'category_id' => $data['category_id'] ?? null,
            'tags' => $this->normalizeTags($data['tags'] ?? null),
            'status' => $data['status'] ?? BaseStatusEnum::DRAFT->value,
        ]);

        if ($request->wantsJson()) {
            return response()->json($page, Response::HTTP_CREATED);
        }

        return redirect()->route('admin.pages.index')->with('success', 'Page created successfully.');
    }

    public function edit(Page $page): InertiaResponse
    {
        return Inertia::render('Admin/pages/edit', [
            'page' => [
                'id' => $page->id,
                'title' => $page->title,
                'slug' => $page->slug,
                'image' => $page->image,
                'content' => $page->content,
                'meta_title' => $page->meta_title,
                'meta_description' => $page->meta_description,
                'meta_keywords' => $page->meta_keywords,
                'category_id' => $page->category_id,
                'tags' => $page->tags,
                'status' => $page->status instanceof BaseStatusEnum ? $page->status->value : (string) $page->status,
            ],
        ]);
    }

    public function update(PageRequest $request, Page $page)
    {
        $data = $request->validated();

        $page = $this->service->update($page->id, [
            'title' => $data['title'],
            'slug' => $this->resolveBaseSlug($data, $page),
            'image' => $data['image'] ?? null,
            'content' => $data['content'] ?? null,
            'meta_title' => $data['meta_title'] ?? null,
            'meta_description' => $data['meta_description'] ?? null,
            'meta_keywords' => $data['meta_keywords'] ?? null,
            'category_id' => $data['category_id'] ?? null,
            'tags' => $this->normalizeTags($data['tags'] ?? null),
            'status' => $data['status'] ?? $page->status,
        ]);

        if ($request->wantsJson()) {
            return response()->json($page);
        }

        return redirect()->route('admin.pages.index')->with('success', 'Page updated successfully.');
    }

    public function destroy(Request $request, Page $page)
    {
        $page->delete();

        if ($request->wantsJson()) {
            return response()->json(['deleted' => true]);
        }

        return redirect()->route('admin.pages.index')->with('success', 'Page deleted successfully.');
    }

    private function resolveBaseSlug(array $data, ?Page $page = null): string
    {
        $slugSource = $data['slug'] ?? $data['title'] ?? '';
        $slug = Str::slug((string) $slugSource);

        if ($slug === '' && $page) {
            return (string) $page->slug;
        }

        if ($slug === '') {
            return 'page-'.Str::random(8);
        }

        $candidate = $slug;
        $counter = 1;

        while ($this->slugExists($candidate, $page)) {
            $candidate = "{$slug}-{$counter}";
            $counter++;
        }

        return $candidate;
    }

    private function slugExists(string $slug, ?Page $page = null): bool
    {
        return Page::query()
            ->where('slug', $slug)
            ->when($page !== null, fn (Builder $query) => $query->whereKeyNot($page->getKey()))
            ->exists();
    }

    private function normalizeTags($value): ?array
    {
        if (is_array($value)) {
            return array_values(array_filter(array_map('trim', $value)));
        }

        if (is_string($value)) {
            return array_values(array_filter(array_map('trim', explode(',', $value))));
        }

        return null;
    }
}
