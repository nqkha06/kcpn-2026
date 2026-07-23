<?php

namespace App\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class ListQueryConfig
{
    public function __construct(
        public ?string $alias = null,
        public array $searchable = [],
        public array $filterable = [],
        public array $sortable = [],
        public array $selectable = [],
        public array $relations = [],
        public array $defaultSort = [],
        public string $dateField = 'created_at',
        public int $maxLimit = 100,
    ) {}

    public function toArray(): array
    {
        return [
            'alias' => $this->alias,
            'searchable' => $this->searchable,
            'filterable' => $this->filterable,
            'sortable' => $this->sortable,
            'selectable' => $this->selectable,
            'relations' => $this->relations,
            'defaultSort' => $this->defaultSort,
            'dateField' => $this->dateField,
            'maxLimit' => $this->maxLimit,
        ];
    }
}
