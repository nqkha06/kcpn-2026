<?php

namespace App\Services\Admin;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Spatie\Permission\Models\Permission;

final class AdminPermissionService
{
    /**
     * @param  array<string, mixed>  $filters
     * @return LengthAwarePaginator<Permission>
     */
    public function paginate(array $filters): LengthAwarePaginator
    {
        $search = trim((string) ($filters['search'] ?? ''));

        return Permission::query()
            ->where('guard_name', 'web')
            ->withCount('roles')
            ->when($search !== '', fn ($query) => $query->where('name', 'like', '%'.$search.'%'))
            ->orderBy(
                (string) ($filters['sort'] ?? 'id'),
                (string) ($filters['direction'] ?? 'desc'),
            )
            ->paginate((int) ($filters['per_page'] ?? 15))
            ->withQueryString();
    }

    /** @return Collection<int, Permission> */
    public function options(): Collection
    {
        return Permission::query()
            ->where('guard_name', 'web')
            ->withCount('roles')
            ->orderBy('name')
            ->get();
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes): Permission
    {
        return Permission::query()->create([
            'name' => $attributes['name'],
            'guard_name' => 'web',
        ])->loadCount('roles');
    }

    public function find(Permission $permission): Permission
    {
        return $permission->loadCount('roles');
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function update(Permission $permission, array $attributes): Permission
    {
        $permission->update(['name' => $attributes['name']]);

        return $permission->loadCount('roles');
    }

    public function delete(Permission $permission): void
    {
        $permission->delete();
    }
}
