<?php

namespace App\Repositories\Contracts;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Interface BaseServiceInterface
 */
interface BaseRepositoryInterface
{
    public function getAll(array $search = []): Collection;

    public function get(array $search = []);

    public function getCount(array $search = []);

    public function create(array $payload);

    public function update(int $id = 0, array $payload = []);

    public function delete(int $id = 0);

    public function getAllPaginated(array $search = [], int $pageSize = 10);

    public function pagination(array $specification = []);

    public function find($id);

    public function findOrFail($value, ?string $column = null);

    public function findMany(array $attributes): Collection;

    public function findFirst(array $attributes): ?Model;

    public function with(array $with);

    public function withCount(array $withCount);

    public function deleteAll(array $search = []): int;

    public function wherePublished();

    public function getModel();

    public function query();

    public function getFillable();

    public function getTranslatable();

    public function updateOrCreate(array $condition = [], array $payload = []);
}
