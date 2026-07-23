<?php

namespace App\Repositories;

use App\Repositories\Contracts\BaseRepositoryInterface;
use App\Traits\Queryable;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Class BaseRepository
 */
class BaseRepository implements BaseRepositoryInterface
{
    use Queryable;

    protected $model;

    public function __construct(
        Model $model
    ) {
        $this->model = $model;
    }

    public function getModel(): Model
    {
        return $this->model;
    }

    public function create(array $payload = [])
    {
        $model = $this->model->create($payload);

        return $model->fresh();
    }

    public function update(int $id = 0, array $payload = [])
    {
        $model = $this->find($id);
        if (! $model) {
            return null;
        }
        $model->update($payload);

        return $model;
    }

    public function delete(int $id = 0)
    {
        return $this->find($id)->delete();
    }

    public function forceDelete(int $id): bool
    {
        $record = $this->model->withTrashed()->find($id);
        if ($record) {
            return $record->forceDelete();
        }

        return false;
    }

    public function createPivot($model, array $payload = [], string $relation = '')
    {
        return $model->{$relation}()->attach($model->id, $payload);
    }

    public function query()
    {
        return $this->model;
    }

    public function getFillable()
    {
        return $this->model->getFillable();
    }

    public function getTranslatable()
    {
        $translatable = property_exists($this->model, 'translatable') ? $this->model->getTranslatable() : [];

        return $translatable;
    }

    public function updateOrCreate(array $condition = [], array $payload = [])
    {
        return $this->model->updateOrCreate($condition, $payload);
    }

    public function pagination(array $specification = [])
    {
        $query = $this->applySpecification($this->freshQuery(), $specification);

        return $query->paginate($specification['pageSize'] ?? 10)->withQueryString();
    }

    public function get(array $specification = [])
    {
        return $this->applySpecification($this->freshQuery(), $specification)->get();
    }

    public function getCount(array $specification = [])
    {
        return $this->applySpecification($this->freshQuery(), $specification)->count();
    }

    protected function applySpecification(Builder $query, array $specification): Builder
    {
        if (! empty($specification['select']) && is_array($specification['select'])) {
            $this->applySelects($query, $specification['select']);
        }

        if (! empty($specification['with']) && is_array($specification['with'])) {
            $query->with($specification['with']);
        }

        $this->applySearch($query, $specification['search'] ?? null);
        $this->applySpecificationFilters($query, $specification['filters'] ?? []);
        $this->applySorts($query, $specification['sort'] ?? []);

        return $query;
    }

    protected function applySearch(Builder $query, ?array $search): void
    {
        if (empty($search['value']) || empty($search['fields']) || ! is_array($search['fields'])) {
            return;
        }

        $keyword = (string) $search['value'];

        $query->where(function (Builder $searchQuery) use ($search, $keyword) {
            foreach ($search['fields'] as $field) {
                if (! is_string($field) || $field === '') {
                    continue;
                }

                if ($this->isMainColumnField($field)) {
                    $searchQuery->orWhere($field, 'like', '%'.$keyword.'%');

                    continue;
                }

                if ($this->isTranslatableField($field)) {
                    $searchQuery->orWhereHas('translations', function (Builder $translationQuery) use ($field, $keyword) {
                        $translationQuery->where($field, 'like', '%'.$keyword.'%');
                    });

                    continue;
                }

                if ($this->isRelationField($field)) {
                    [$relation, $column] = $this->splitRelationField($field);

                    $searchQuery->orWhereHas($relation, function (Builder $relationQuery) use ($column, $keyword) {
                        $relationQuery->where($column, 'like', '%'.$keyword.'%');
                    });
                }
            }
        });
    }

    protected function applySpecificationFilters(Builder $query, array $filters): void
    {
        foreach (($filters['simple'] ?? []) as $field => $value) {
            if ($value === null || $value === '') {
                continue;
            }

            $this->applyFieldConstraint($query, $field, function (Builder $builder, string $column) use ($value): void {
                if (is_array($value)) {
                    $builder->whereIn($column, $value);

                    return;
                }

                $builder->where($column, '=', $value);
            });
        }

        foreach (($filters['date'] ?? []) as $field => $dateFilter) {
            if (! is_array($dateFilter)) {
                continue;
            }

            $from = $dateFilter['from'] ?? null;
            $to = $dateFilter['to'] ?? null;

            if (! $from && ! $to) {
                continue;
            }

            $this->applyFieldConstraint($query, $field, function (Builder $builder, string $column) use ($from, $to): void {
                if ($from && $to) {
                    $builder->whereBetween($column, [
                        Carbon::parse($from)->startOfDay(),
                        Carbon::parse($to)->endOfDay(),
                    ]);

                    return;
                }

                if ($from) {
                    $builder->where($column, '>=', Carbon::parse($from)->startOfDay());
                }

                if ($to) {
                    $builder->where($column, '<=', Carbon::parse($to)->endOfDay());
                }
            });
        }

        foreach (($filters['complex'] ?? []) as $field => $conditions) {
            if (! is_array($conditions)) {
                continue;
            }

            foreach ($conditions as $operator => $value) {
                $this->applyComplexFilter($query, $field, (string) $operator, $value);
            }
        }
    }

    protected function applyComplexFilter(Builder $query, string $field, string $operator, mixed $value): void
    {
        if ($value === null) {
            return;
        }

        $normalizedOperator = strtolower(trim($operator));

        $this->applyFieldConstraint($query, $field, function (Builder $builder, string $column) use ($normalizedOperator, $value): void {
            switch ($normalizedOperator) {
                case 'eq':
                case '=':
                    $builder->where($column, '=', $value);
                    break;
                case 'ne':
                case '!=':
                    $builder->where($column, '<>', $value);
                    break;
                case 'gt':
                case '>':
                    $builder->where($column, '>', $value);
                    break;
                case 'gte':
                case '>=':
                    $builder->where($column, '>=', $value);
                    break;
                case 'lt':
                case '<':
                    $builder->where($column, '<', $value);
                    break;
                case 'lte':
                case '<=':
                    $builder->where($column, '<=', $value);
                    break;
                case 'like':
                    $builder->where($column, 'like', '%'.$value.'%');
                    break;
                case 'starts_with':
                    $builder->where($column, 'like', $value.'%');
                    break;
                case 'ends_with':
                    $builder->where($column, 'like', '%'.$value);
                    break;
                case 'in':
                    if (is_array($value)) {
                        $builder->whereIn($column, $value);
                    }
                    break;
                case 'not_in':
                    if (is_array($value)) {
                        $builder->whereNotIn($column, $value);
                    }
                    break;
                case 'between':
                    if (is_array($value) && count($value) === 2) {
                        $builder->whereBetween($column, array_values($value));
                    }
                    break;
                case 'null':
                    $builder->whereNull($column);
                    break;
                case 'not_null':
                    $builder->whereNotNull($column);
                    break;
                default:
                    $builder->where($column, '=', $value);
                    break;
            }
        });
    }

    protected function applySorts(Builder $query, array $sorts): void
    {
        $normalizedSorts = $this->normalizeSortConfig($sorts);
        if (empty($normalizedSorts)) {
            $query->orderBy('id', 'desc');

            return;
        }

        foreach ($normalizedSorts as $sort) {
            $field = $sort['field'];
            $direction = strtolower($sort['direction']) === 'desc' ? 'desc' : 'asc';

            if ($this->isRelationField($field)) {
                [$relation, $column] = $this->splitRelationField($field);
                $query->withAggregate($relation, $column);

                $aggregateColumn = str_replace('.', '_', $relation).'_'.$column;
                $query->orderBy($aggregateColumn, $direction);

                continue;
            }

            if ($this->isMainColumnField($field)) {
                $query->orderBy($field, $direction);

                continue;
            }

            if ($this->isTranslatableField($field)) {
                $query->withAggregate('translations', $field);
                $query->orderBy('translations_'.$field, $direction);
            }
        }
    }

    protected function normalizeSortConfig(array $sorts): array
    {
        $normalized = [];

        if (isset($sorts[0]) && is_string($sorts[0]) && isset($sorts[1]) && is_string($sorts[1])) {
            return [[
                'field' => $sorts[0],
                'direction' => strtolower($sorts[1]) === 'desc' ? 'desc' : 'asc',
            ]];
        }

        foreach ($sorts as $key => $sort) {
            if (is_array($sort)) {
                $field = $sort['field'] ?? null;
                $direction = $sort['direction'] ?? $sort['order'] ?? 'asc';
            } elseif (is_string($key) && is_string($sort)) {
                $field = $key;
                $direction = $sort;
            } else {
                continue;
            }

            if (! is_string($field) || $field === '') {
                continue;
            }

            $normalized[] = [
                'field' => $field,
                'direction' => strtolower((string) $direction) === 'desc' ? 'desc' : 'asc',
            ];
        }

        return $normalized;
    }

    protected function applyFieldConstraint(Builder $query, string $field, callable $constraint): void
    {
        if ($this->isMainColumnField($field)) {
            $constraint($query, $field);

            return;
        }

        if ($this->isTranslatableField($field)) {
            $query->whereHas('translations', function (Builder $translationQuery) use ($field, $constraint): void {
                $constraint($translationQuery, $field);
            });

            return;
        }

        if (! $this->isRelationField($field)) {
            return;
        }

        [$relation, $column] = $this->splitRelationField($field);

        $query->whereHas($relation, function (Builder $relationQuery) use ($column, $constraint): void {
            $constraint($relationQuery, $column);
        });
    }

    protected function splitRelationField(string $field): array
    {
        $segments = explode('.', $field);
        $column = array_pop($segments);
        $relation = implode('.', $segments);

        return [$relation, $column];
    }

    protected function isRelationField(string $field): bool
    {
        return str_contains($field, '.');
    }

    protected function isMainColumnField(string $field): bool
    {
        if (in_array($field, $this->getFillable(), true) || $field === $this->model->getKeyName()) {
            return true;
        }

        if ($this->model->usesTimestamps() && in_array($field, ['created_at', 'updated_at'], true)) {
            return true;
        }

        if (method_exists($this->model, 'getDeletedAtColumn') && $field === $this->model->getDeletedAtColumn()) {
            return true;
        }

        return false;
    }

    protected function isTranslatableField(string $field): bool
    {
        return in_array($field, $this->getTranslatable(), true);
    }

    protected function freshQuery(): Builder
    {
        unset($this->query);

        return $this->getQuery();
    }

    protected function applySelects(Builder $query, array $fields): void
    {
        $columns = [];
        foreach ($fields as $field) {
            if (! is_string($field) || $field === '' || $this->isRelationField($field)) {
                continue;
            }

            if ($this->isMainColumnField($field)) {
                $columns[] = $field;
            }
        }

        $keyName = $this->model->getKeyName();
        if (! in_array($keyName, $columns, true)) {
            $columns[] = $keyName;
        }

        if ($columns !== []) {
            $query->select(array_values(array_unique($columns)));
        }
    }
}
