<?php

namespace App\Services\Admin;

use App\Models\Menu;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

final class AdminMenuService
{
    /**
     * @param  array<string, mixed>  $filters
     * @return LengthAwarePaginator<Menu>
     */
    public function paginate(array $filters): LengthAwarePaginator
    {
        $query = Menu::query()->with('parent:id,title,canonical');
        $search = trim((string) ($filters['search'] ?? ''));

        if ($search !== '') {
            $query->where(function (Builder $builder) use ($search): void {
                $builder->where('title', 'like', '%'.$search.'%')
                    ->orWhere('url', 'like', '%'.$search.'%');
            });
        }

        foreach (['status', 'canonical', 'parent_id'] as $field) {
            if (isset($filters[$field]) && $filters[$field] !== '') {
                $query->where($field, $filters[$field]);
            }
        }

        return $query
            ->orderBy(
                (string) ($filters['sort'] ?? 'sort_order'),
                (string) ($filters['direction'] ?? 'asc'),
            )
            ->orderBy('id')
            ->paginate((int) ($filters['per_page'] ?? 15))
            ->withQueryString();
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes): Menu
    {
        $menu = Menu::query()->create($this->payload($attributes));

        return $menu->load('parent:id,title,canonical');
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function update(Menu $menu, array $attributes): Menu
    {
        $menu->update($this->payload($attributes));

        return $menu->refresh()->load('parent:id,title,canonical');
    }

    public function find(Menu $menu): Menu
    {
        return $menu->load('parent:id,title,canonical');
    }

    public function delete(Menu $menu): void
    {
        $menu->delete();
    }

    /**
     * @return Collection<int, Menu>
     */
    public function parentOptions(?int $excludedMenuId = null): Collection
    {
        return Menu::query()
            ->whereNull('parent_id')
            ->when($excludedMenuId !== null, fn (Builder $query) => $query->whereKeyNot($excludedMenuId))
            ->orderBy('title')
            ->get(['id', 'title', 'canonical']);
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @return array<string, mixed>
     */
    private function payload(array $attributes): array
    {
        $parentId = $attributes['parent_id'] ?? null;
        $canonical = (string) $attributes['canonical'];

        if ($parentId !== null) {
            $parent = Menu::query()->find($parentId);
            $canonical = $parent?->canonical ?? $canonical;
        }

        $url = isset($attributes['url']) ? trim((string) $attributes['url']) : '';

        return [
            'title' => $attributes['title'],
            'url' => $url === '' ? null : $url,
            'parent_id' => $parentId,
            'canonical' => $canonical,
            'sort_order' => (int) ($attributes['sort_order'] ?? 0),
            'target' => $attributes['target'],
            'status' => $attributes['status'],
        ];
    }
}
