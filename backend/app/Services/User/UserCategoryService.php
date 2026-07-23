<?php

namespace App\Services\User;

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

final class UserCategoryService
{
    /**
     * @return Collection<int, Category>
     */
    public function active(User $user): Collection
    {
        return Category::query()
            ->where('status', 'active')
            ->where(function ($query) use ($user): void {
                $query->whereNull('user_id')->orWhere('user_id', $user->id);
            })
            ->orderByRaw('user_id IS NULL DESC')
            ->orderBy('name')
            ->get(['id', 'user_id', 'name', 'color', 'description', 'status']);
    }

    /** @param array<string, mixed> $attributes */
    public function create(User $user, array $attributes): Category
    {
        return $user->categories()->create([
            ...$attributes,
            'status' => 'active',
        ]);
    }

    /** @param array<string, mixed> $attributes */
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
