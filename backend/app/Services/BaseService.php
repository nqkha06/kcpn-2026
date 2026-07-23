<?php

namespace App\Services;

use App\Attributes\ListQueryConfig;
use App\Repositories\Contracts\BaseRepositoryInterface as BaseRepository;
use App\Traits\HasSpecificationBuilder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

// phương thức CRUD
abstract class BaseService
{
    use HasSpecificationBuilder;

    protected Request $request;

    protected mixed $result;

    protected BaseRepository $repository;

    protected Model $model;

    protected $modelData = [];

    protected ?string $queryAlias = null;

    protected ?ListQueryConfig $querySetup = null;

    public function __construct(BaseRepository $repository)
    {
        $this->repository = $repository;
        $this->bootstrapListQueryConfiguration();
    }

    // ====== MAIN DATA METHODS ======
    public function getAll(array|Request $request = [])
    {
        $this->setRequest($this->normalizeListRequest($request));
        $specification = $this->specificationBuilder();

        $this->result = $this->repository->get($specification);

        return $this->getResult();
    }

    public function getCount(array|Request $request = [])
    {
        $this->setRequest($this->normalizeListRequest($request));
        $specification = $this->specificationBuilder();

        $this->result = $this->repository->getCount($specification);

        return $this->getResult();
    }

    public function paginate(array|Request $request = [])
    {
        $this->setRequest($this->normalizeListRequest($request));
        $specification = $this->specificationBuilder();

        $this->result = $this->repository->pagination($specification);

        return $this->getResult();
    }

    public function find(int $id, array $with = [])
    {
        $record = $this->repository->with($with)->find($id);
        if (! $record) {
            abort(404);
        }

        return $record;
    }

    public function create(array|Request $payload)
    {
        try {
            $result = $this->beginTransaction()
                ->setRequest($this->normalizeRequest($payload))
                ->prepareModel()
                ->beforeSave()
                ->saveModel()
                ->saveRelations()
                ->afterSave()
                ->commit()
                ->getResult();

            return $result;
        } catch (\Throwable $th) {
            $this->rollBack();
            Log::error($th->getMessage());

            return false;
        }
    }

    public function update(int|string $id, array|Request $payload)
    {
        try {
            $result = $this->beginTransaction()
                ->setRequest($this->normalizeRequest($payload))
                ->prepareModel()
                ->beforeSave()
                ->saveModel((int) $id)
                ->saveRelations()
                ->afterSave()
                ->commit()
                ->getResult();

            return $result;
        } catch (\Throwable $th) {
            $this->rollBack();
            Log::error($th->getMessage());

            return false;
        }
    }

    public function destroy(int|string $id): bool
    {
        try {
            $deleted = $this->repository->delete($id);

            return (bool) $deleted;
        } catch (\Throwable $th) {
            Log::error($th->getMessage());

            return false;
        }
    }

    // ====== INTERNAL HELPERS ======
    protected function prepareModel(): static
    {
        $this->modelData = $this->request->only($this->repository->getFillable());

        return $this;
    }

    public function getResult()
    {
        return $this->result;
    }

    protected function saveModel(?int $id = null): static
    {
        $this->model = $id
            ? $this->repository->update($id, $this->modelData)
            : $this->repository->create($this->modelData);

        $this->result = $this->model;

        return $this;
    }

    protected function saveRelations(): static
    {
        $relations = $this->repository->getModel()->relatables ?? [];
        foreach ($relations as $relation) {
            if ($this->request->has($relation)) {
                $type = $this->repository->getModel()->{$relation}() instanceof \Illuminate\Database\Eloquent\Relations\BelongsToMany ? 'many' : 'one';

                switch ($type) {
                    case 'many':
                        $this->model->{$relation}()->sync($this->request->input($relation, []));
                        break;
                    case 'one':
                        $this->model->{$relation}()->associate($this->request->input($relation));
                        $this->model->save();
                        break;
                }
            }
        }

        return $this;
    }

    protected function beforeSave(): static
    {
        return $this;
    }

    protected function afterSave(): static
    {
        return $this;
    }

    protected function setRequest(Request $request): static
    {
        $this->request = $request;

        return $this;
    }

    // ====== TRANSACTION HELPERS ======
    protected function beginTransaction()
    {
        DB::beginTransaction();

        return $this;
    }

    protected function commit()
    {
        DB::commit();

        return $this;
    }

    protected function rollBack()
    {
        DB::rollBack();

        return $this;
    }

    public function getListQuerySetup(): array
    {
        return [
            'alias' => $this->queryAlias,
            'searchable' => $this->searchable,
            'filterable' => $this->filterable,
            'sortable' => $this->sortable,
            'selectable' => $this->selectable,
            'relations' => $this->includable,
            'defaultSort' => $this->defaultSort,
            'dateField' => $this->dateField,
            'maxLimit' => $this->maxPageSize,
        ];
    }

    private function specificationBuilder(): array
    {
        return $this->buildSpecification($this->request);
    }

    private function normalizeRequest(array|Request $input = []): Request
    {
        return $input instanceof Request
            ? $input
            : new Request($input);
    }

    private function normalizeListRequest(array|Request $input = []): Request
    {
        $request = $this->normalizeRequest($input);
        $payload = $input instanceof Request ? $request->query() : $input;

        if (! is_array($payload)) {
            $payload = [];
        }

        $payload = $this->normalizeListPayload($payload);

        return new Request($payload);
    }

    protected function normalizeListPayload(array $payload): array
    {
        $normalized = $payload;

        if (! array_key_exists('search', $normalized) && array_key_exists('keyword', $normalized)) {
            $normalized['search'] = $normalized['keyword'];
        }

        if (! array_key_exists('limit', $normalized) && array_key_exists('per_page', $normalized)) {
            $normalized['limit'] = $normalized['per_page'];
        }

        if (! array_key_exists('sort', $normalized)) {
            $legacySortBy = $normalized['sort_by'] ?? $normalized['sort_field'] ?? null;
            if (is_string($legacySortBy) && trim($legacySortBy) !== '') {
                $legacyDirection = strtolower((string) ($normalized['sort_direction'] ?? $normalized['sort_order'] ?? 'asc'));
                $normalized['sort'] = ($legacyDirection === 'desc' ? '-' : '').trim($legacySortBy);
            }
        }

        if (isset($normalized['include']) && is_array($normalized['include'])) {
            $normalized['include'] = implode(',', array_filter(array_map('trim', $normalized['include'])));
        }

        if (isset($normalized['fields']) && is_array($normalized['fields'])) {
            $normalized['fields'] = implode(',', array_filter(array_map('trim', $normalized['fields'])));
        }

        return $normalized;
    }

    public function with(array $relations): static
    {
        $this->with = $relations;

        return $this;
    }

    protected function bootstrapListQueryConfiguration(): void
    {
        $attribute = $this->resolveListQueryAttribute();
        if (! $attribute) {
            return;
        }

        $this->querySetup = $attribute;
        $this->queryAlias = $attribute->alias;

        $this->searchable = $attribute->searchable;
        $this->filterable = $attribute->filterable;
        $this->sortable = $attribute->sortable;
        $this->selectable = $attribute->selectable;
        $this->includable = $attribute->relations;
        $this->defaultSort = $attribute->defaultSort;
        $this->dateField = $attribute->dateField;
        $this->maxPageSize = $attribute->maxLimit;
    }

    protected function resolveListQueryAttribute(): ?ListQueryConfig
    {
        $reflection = new \ReflectionClass($this);
        $attributes = $reflection->getAttributes(ListQueryConfig::class);
        if ($attributes === []) {
            return null;
        }

        $instance = $attributes[0]->newInstance();

        return $instance instanceof ListQueryConfig ? $instance : null;
    }
}
