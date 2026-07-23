<?php

namespace App\Services\Admin;

use App\Models\Category;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

final class AdminCategoryService
{
    /**
     * @param  array<string, mixed>  $filters
     * @return LengthAwarePaginator<Category>
     */
    public function paginate(array $filters): LengthAwarePaginator
    {
        $query = Category::query()->whereNull('user_id');
        $search = trim((string) ($filters['search'] ?? ''));

        if ($search !== '') {
            $query->where(function (Builder $builder) use ($search): void {
                $builder->where('name', 'like', '%'.$search.'%')
                    ->orWhere('description', 'like', '%'.$search.'%');
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
    public function create(array $attributes): Category
    {
        return Category::query()->create([
            ...$attributes,
            'user_id' => null,
        ]);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function update(Category $category, array $attributes): Category
    {
        $category->update($attributes);

        return $category->refresh();
    }

    public function delete(Category $category): void
    {
        $category->delete();
    }
}
