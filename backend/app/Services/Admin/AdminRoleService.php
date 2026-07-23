<?php

namespace App\Services\Admin;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

final class AdminRoleService
{
    /**
     * @param  array<string, mixed>  $filters
     * @return LengthAwarePaginator<Role>
     */
    public function paginate(array $filters): LengthAwarePaginator
    {
        $search = trim((string) ($filters['search'] ?? ''));

        return Role::query()
            ->where('guard_name', 'web')
            ->with('permissions')
            ->when($search !== '', fn ($query) => $query->where('name', 'like', '%'.$search.'%'))
            ->orderBy(
                (string) ($filters['sort'] ?? 'id'),
                (string) ($filters['direction'] ?? 'desc'),
            )
            ->paginate((int) ($filters['per_page'] ?? 15))
            ->withQueryString();
    }

    /** @return Collection<int, Role> */
    public function options(): Collection
    {
        return Role::query()
            ->where('guard_name', 'web')
            ->with('permissions')
            ->orderBy('name')
            ->get();
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes): Role
    {
        return DB::transaction(function () use ($attributes): Role {
            $role = Role::query()->create([
                'name' => $attributes['name'],
                'guard_name' => 'web',
            ]);

            if (array_key_exists('permissions', $attributes)) {
                $role->syncPermissions($attributes['permissions']);
            }

            return $this->loadRelations($role);
        });
    }

    public function find(Role $role): Role
    {
        return $this->loadRelations($role);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function update(Role $role, array $attributes): Role
    {
        return DB::transaction(function () use ($role, $attributes): Role {
            $role->update(['name' => $attributes['name']]);

            if (array_key_exists('permissions', $attributes)) {
                $role->syncPermissions($attributes['permissions']);
            }

            return $this->loadRelations($role);
        });
    }

    public function delete(Role $role): void
    {
        $role->delete();
    }

    private function loadRelations(Role $role): Role
    {
        return $role->load('permissions');
    }
}
