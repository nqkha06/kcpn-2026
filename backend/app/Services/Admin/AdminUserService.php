<?php

namespace App\Services\Admin;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

final class AdminUserService
{
    /**
     * @param  array<string, mixed>  $filters
     * @return LengthAwarePaginator<User>
     */
    public function paginate(array $filters): LengthAwarePaginator
    {
        $query = User::query()->with('roles.permissions');
        $search = trim((string) ($filters['search'] ?? ''));

        if ($search !== '') {
            $query->where(function (Builder $builder) use ($search): void {
                $builder->where('name', 'like', '%'.$search.'%')
                    ->orWhere('email', 'like', '%'.$search.'%');

                if (ctype_digit($search)) {
                    $builder->orWhereKey((int) $search);
                }
            });
        }

        if (! empty($filters['email'])) {
            $query->where('email', 'like', '%'.trim((string) $filters['email']).'%');
        }

        if (! empty($filters['role'])) {
            $query->whereHas('roles', function (Builder $builder) use ($filters): void {
                $builder->where('name', $filters['role'])->where('guard_name', 'web');
            });
        }

        if (! empty($filters['created_date'])) {
            $query->whereDate('created_at', $filters['created_date']);
        } else {
            if (! empty($filters['created_from'])) {
                $query->whereDate('created_at', '>=', $filters['created_from']);
            }

            if (! empty($filters['created_to'])) {
                $query->whereDate('created_at', '<=', $filters['created_to']);
            }
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
    public function create(array $attributes): User
    {
        return DB::transaction(function () use ($attributes): User {
            $user = User::query()->create([
                'name' => $attributes['name'],
                'email' => $attributes['email'],
                'password' => Hash::make((string) $attributes['password']),
            ]);

            if (array_key_exists('roles', $attributes)) {
                $user->syncRoles($attributes['roles']);
            }

            return $this->loadRelations($user);
        });
    }

    public function find(User $user): User
    {
        return $this->loadRelations($user);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function update(User $user, array $attributes): User
    {
        return DB::transaction(function () use ($user, $attributes): User {
            $payload = [
                'name' => $attributes['name'],
                'email' => $attributes['email'],
            ];

            if (! empty($attributes['password'])) {
                $payload['password'] = Hash::make((string) $attributes['password']);
            }

            $user->update($payload);

            if (array_key_exists('roles', $attributes)) {
                $user->syncRoles($attributes['roles']);
            }

            return $this->loadRelations($user);
        });
    }

    public function delete(User $user): void
    {
        $user->delete();
    }

    private function loadRelations(User $user): User
    {
        return $user->load('roles.permissions');
    }
}
