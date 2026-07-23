<?php

namespace App\Traits;

use Illuminate\Http\Request;

trait HasSpecificationBuilder
{
    protected $with = [];

    protected $includable = [];

    protected $selectable = [];

    protected $searchable = [];

    protected $filterable = [];

    protected $sortable = [];

    protected $defaultPageSize = 10;

    protected $maxPageSize = 100;

    protected $defaultSort = [];

    protected $dateField = 'created_at';

    protected $dateFilterable = ['created_at', 'updated_at'];

    // Allowed operators for filtering
    protected $allowedOperators = [
        'eq', '=', 'ne', '!=', 'gt', '>', 'gte', '>=',
        'lt', '<', 'lte', '<=', 'like', 'starts_with',
        'ends_with', 'in', 'not_in', 'between', 'null',
        'not_null', 'exists', 'not_exists', 'date_range', 'date_preset',
    ];

    protected function buildSpecification(Request $request): array
    {
        $keyword = $this->parseSearchKeyword($request);
        $includes = $this->parseIncludes($request);
        $selects = $this->parseSelectFields($request);
        $parsedFilters = $this->parseFilters($request);
        $dateFilters = $this->parseDateFilters($request);
        $sort = $this->parseSort($request);

        $status = $request->input('status', null);
        if ($status !== null && in_array('status', $this->filterable)) {
            $parsedFilters['simple']['status'] = $this->normalizeFilterValue($status);
        }

        $pageSize = (int) $request->input('limit', $request->input('per_page', $this->defaultPageSize));
        $pageSize = max(1, min($pageSize, $this->maxPageSize));

        return [
            'with' => $includes,
            'select' => $selects,
            'search' => $keyword ? [
                'value' => $keyword,
                'fields' => $this->searchable,
            ] : null,
            'filters' => [
                'simple' => array_filter($parsedFilters['simple'] ?? []),
                'complex' => array_filter($parsedFilters['complex'] ?? []),
                'date' => array_filter($dateFilters ?? []),
            ],
            'sort' => $sort,
            'pageSize' => $pageSize,
        ];
    }

    /**
     * Parse complex filters from request
     * Handles formats like: user[gt]=1, date[gte]=2023-01-01, status[in]=active,pending
     */
    protected function parseFilters(Request $request): array
    {
        $filters = [
            'simple' => [],
            'complex' => [],
        ];

        $requestFilters = $request->input('filters', []);
        if (is_array($requestFilters)) {
            foreach ($requestFilters as $field => $value) {
                if (! in_array($field, $this->filterable, true)) {
                    continue;
                }

                if (is_array($value)) {
                    foreach ($value as $operator => $operand) {
                        if ($this->isValidOperator((string) $operator)) {
                            $filters['complex'][$field][$operator] = $operand;
                        }
                    }

                    continue;
                }

                if ($value !== null && $value !== '') {
                    $filters['simple'][$field] = $this->normalizeFilterValue($value);
                }
            }
        }

        $allInputs = $request->all();
        foreach ($allInputs as $field => $value) {
            if (in_array($field, ['filters', 'include', 'sort', 'search', 'fields', 'page', 'limit', 'per_page', 'keyword', 'sort_field', 'sort_order', 'sort_by', 'sort_direction'], true)) {
                continue;
            }

            if (is_array($value) && ! is_null($value) && $value !== '') {
                foreach ($value as $operator => $val) {
                    if ($this->isValidOperator((string) $operator)) {
                        if (in_array($field, $this->filterable, true)) {
                            $filters['complex'][$field][$operator] = $val;
                        }
                    }
                }
            } else {
                if (in_array($field, $this->filterable, true) && ! in_array($field, ['keyword', 'sort_field', 'sort_order', 'page', 'page_size', 'sort_by', 'sort_direction'], true)) {
                    $filters['simple'][$field] = $this->normalizeFilterValue($value);
                }
            }
        }

        return $filters;
    }

    protected function parseSearchKeyword(Request $request): ?string
    {
        $search = $request->input('search', $request->input('keyword'));
        if (! is_string($search)) {
            return null;
        }

        $search = trim($search);

        return $search === '' ? null : $search;
    }

    protected function parseIncludes(Request $request): array
    {
        $allowedIncludes = array_values(array_unique(array_merge($this->with, $this->includable)));
        if (empty($allowedIncludes)) {
            return [];
        }

        $requested = $request->input('include', []);
        if (is_string($requested)) {
            $requested = array_filter(array_map('trim', explode(',', $requested)));
        }

        if (! is_array($requested)) {
            $requested = [];
        }

        if (empty($requested)) {
            return $this->with;
        }

        $filteredRequested = array_values(array_filter($requested, static fn ($relation): bool => in_array($relation, $allowedIncludes, true)));

        return array_values(array_unique(array_merge($this->with, $filteredRequested)));
    }

    protected function parseSort(Request $request): array
    {
        $requestedSort = $request->input('sort', []);
        $sorts = [];

        if (is_string($requestedSort)) {
            $requestedSort = array_filter(array_map('trim', explode(',', $requestedSort)));
        }

        if (is_array($requestedSort) && ! empty($requestedSort)) {
            foreach ($requestedSort as $item) {
                if (! is_string($item) || trim($item) === '') {
                    continue;
                }

                $field = ltrim($item, '-');
                if (! in_array($field, $this->sortable, true)) {
                    continue;
                }

                $sorts[] = [
                    'field' => $field,
                    'direction' => str_starts_with($item, '-') ? 'desc' : 'asc',
                ];
            }

            return $sorts;
        }

        if (empty($this->defaultSort)) {
            return [];
        }

        $defaultSorts = is_array($this->defaultSort) && isset($this->defaultSort['field'])
            ? [$this->defaultSort]
            : $this->defaultSort;

        return array_values(array_filter($defaultSorts, function ($item): bool {
            if (! is_array($item)) {
                return false;
            }

            $field = $item['field'] ?? null;
            if (! is_string($field) || ! in_array($field, $this->sortable, true)) {
                return false;
            }

            return true;
        }));
    }

    /**
     * Parse date filters from request
     * Supports formats like:
     * - date_range[created_at]=today
     * - date_range[created_at][preset]=this_week
     * - date_range[created_at][range][]=2024-01-01&date_range[created_at][range][]=2024-01-31
     */
    protected function parseDateFilters(Request $request): array
    {
        $dateFilters = [];

        if (is_string($this->dateField) && $this->dateField !== '') {
            $from = $request->input('created_from');
            $to = $request->input('created_to');
            if ($from || $to) {
                $dateFilters[$this->dateField] = [
                    'from' => $from,
                    'to' => $to,
                ];
            }
        }

        foreach ($this->dateFilterable as $field) {
            if (! is_string($field) || $field === '' || array_key_exists($field, $dateFilters)) {
                continue;
            }

            $start = $request->input($field.'_from');
            $end = $request->input($field.'_to');
            if ($start || $end) {
                $dateFilters[$field] = [
                    'from' => $start,
                    'to' => $end,
                ];
            }
        }

        return $dateFilters;
    }

    /**
     * Check if operator is valid
     */
    protected function isValidOperator(string $operator): bool
    {
        return in_array($operator, $this->allowedOperators, true) || is_numeric($operator);
    }

    protected function parseSelectFields(Request $request): array
    {
        $fields = $request->input('fields');
        if (is_string($fields)) {
            $requestedFields = array_filter(array_map('trim', explode(',', $fields)));

            return array_values(array_filter($requestedFields, fn (string $field): bool => in_array($field, $this->selectable, true)));
        }

        if (! empty($this->selectable)) {
            return $this->selectable;
        }

        return [];
    }

    protected function normalizeFilterValue(mixed $value): mixed
    {
        if (! is_string($value)) {
            return $value;
        }

        $normalized = trim($value);
        if ($normalized === '' || ! str_contains($normalized, ',')) {
            return $normalized;
        }

        return array_values(array_filter(array_map('trim', explode(',', $normalized)), static fn (string $item): bool => $item !== ''));
    }
}
