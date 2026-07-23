<?php

namespace App\Services\Admin;

use App\Enums\BaseStatusEnum;
use App\Models\Page;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

final class AdminPageService
{
    /**
     * @param  array<string, mixed>  $filters
     * @return LengthAwarePaginator<Page>
     */
    public function paginate(array $filters): LengthAwarePaginator
    {
        $query = Page::query()->with([
            'category:id,name',
            'user:id,name,email',
        ]);

        $search = trim((string) ($filters['search'] ?? ''));

        if ($search !== '') {
            $query->where(function (Builder $builder) use ($search): void {
                $builder->where('title', 'like', '%'.$search.'%')
                    ->orWhere('slug', 'like', '%'.$search.'%');
            });
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query
            ->orderBy(
                (string) ($filters['sort'] ?? 'created_at'),
                (string) ($filters['direction'] ?? 'desc'),
            )
            ->paginate((int) ($filters['per_page'] ?? 15))
            ->withQueryString();
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(User $user, array $attributes): Page
    {
        $page = Page::query()->create($this->payload($attributes, $user));

        return $this->loadRelations($page);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function update(Page $page, array $attributes): Page
    {
        $page->update($this->payload($attributes, $page->user, $page));

        return $this->loadRelations($page);
    }

    public function find(Page $page): Page
    {
        return $this->loadRelations($page);
    }

    public function delete(Page $page): void
    {
        $page->delete();
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @return array<string, mixed>
     */
    private function payload(array $attributes, ?User $user, ?Page $page = null): array
    {
        return [
            'user_id' => $page?->user_id ?? $user?->id,
            'title' => $attributes['title'],
            'slug' => $this->resolveSlug($attributes, $page),
            'image' => $attributes['image'] ?? null,
            'content' => $attributes['content'] ?? null,
            'meta_title' => $attributes['meta_title'] ?? null,
            'meta_description' => $attributes['meta_description'] ?? null,
            'meta_keywords' => $attributes['meta_keywords'] ?? null,
            'category_id' => $attributes['category_id'] ?? null,
            'tags' => $this->normalizeTags($attributes['tags'] ?? null),
            'status' => $attributes['status'] ?? ($page?->status ?? BaseStatusEnum::DRAFT->value),
        ];
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function resolveSlug(array $attributes, ?Page $page): string
    {
        $baseSlug = Str::slug((string) ($attributes['slug'] ?? $attributes['title'] ?? ''));

        if ($baseSlug === '') {
            $baseSlug = $page?->slug ?? 'page-'.Str::random(8);
        }

        $candidate = $baseSlug;
        $counter = 1;

        while (Page::query()
            ->where('slug', $candidate)
            ->when($page !== null, fn (Builder $query) => $query->whereKeyNot($page->id))
            ->exists()) {
            $candidate = $baseSlug.'-'.$counter;
            $counter++;
        }

        return $candidate;
    }

    private function normalizeTags(mixed $tags): ?array
    {
        if (is_string($tags)) {
            $tags = explode(',', $tags);
        }

        if (! is_array($tags)) {
            return null;
        }

        $normalized = collect($tags)
            ->map(fn (mixed $tag): string => trim((string) $tag))
            ->filter(fn (string $tag): bool => $tag !== '')
            ->unique()
            ->values()
            ->all();

        return $normalized === [] ? null : $normalized;
    }

    private function loadRelations(Page $page): Page
    {
        return $page->load([
            'category:id,name',
            'user:id,name,email',
        ]);
    }
}
